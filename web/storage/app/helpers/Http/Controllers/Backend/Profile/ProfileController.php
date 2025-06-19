<?php

namespace App\Http\Controllers\Backend\Profile;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Config;
use Exception;
use File;


class ProfileController extends Controller
{
    /**
     * Summary of validator
     * @param mixed $data
     * @param mixed $type
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $type)
    {
        // Determine if password validation is required depending on the calling
        return Validator::make($data, [
            // Add unique validation to prevent for duplicate email while forcing unique rule to ignore a given ID
            'email' => $type == 'create' ? 'email|required|string|max:255|unique:users' : 'email|required|string|max:255|unique:users,email,' . $data['id'],
            // (update: not required, create: required)
            'password' => $type == 'create' ? 'required|string|min:6|max:255' : '',
            'name' => $type == 'create' || Auth::user()->role == 1 || Auth::user()->role == 4 ? 'required|string|max:255|unique:histories,name' : 'required|string|max:255|unique:histories,name,' . $data['qr_id'],
        ]);
    }

    /**
     * Summary of getRoute
     * @return string
     */
    private function getRoute()
    {
        return 'profile';
    }

    /**
     * Summary of details
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function details()
    {
        $userId = Auth::user()->id;
        $data = User::find($userId);
        $data->form_action = $this->getRoute() . '.update';
        $data->button_text = 'Edit';

        // Get history id by name
        $getHistory = History::where('name', $data->name)
            ->first();
        $getHistory ? $data->qr_id = $getHistory->id : 0;

        return view('backend.profile.form', [
            'data' => $data
        ]);
    }

    /**
     * Summary of update
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $new = $request->all();
        try {
            $currentData = User::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();

                if (empty($new['password'])) {
                    $new['password'] = $currentData['password'];
                } else {
                    $new['password'] = bcrypt($new['password']);
                }

                // Handle image deletion and upload in a single block
                if ($request->get('image_delete') != null) {
                    $new['image'] = 'default-user.png'; // Set to default only if deleted

                    if ($currentData->{'image'} != 'default-user.png') {
                        $filePath = Config::get('const.UPLOAD_PATH') . $currentData['image'];
                        if (File::exists($filePath)) {
                            File::delete($filePath);
                        }
                    }
                }

                // If new image is being uploaded
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    ${'image'} = $currentData->id . "_image." . $file->getClientOriginalExtension();
                    $new['image'] = ${'image'};
                    $pathPhoto = Config::get('const.UPLOAD_PATH');
                    $file->move($pathPhoto, ${'image'});
                }

                if (!$request->hasFile('image') && $request->get('image_delete') == null) {
                    $new['image'] = $currentData['image']; // Retain old image if no new image and not deleted
                }

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update Profile");

                // If user update role 2 or 3 means admin or staff will upadate also the history QR
                if ($currentData->role == 2 || $currentData->role == 3) {
                    $currentDataHistory = History::find($request->get('qr_id'));
                    if ($currentDataHistory) {
                        $currentDataHistory->update($new);
                        $controller->saveLog($new, "Update history QR");
                    }
                }

                // Update
                $currentData->update($new);
                return redirect()->route($this->getRoute() . '.details')->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute() . '.details')->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            \Log::error("Update profile failed: " . $e->getMessage());
            return redirect()->route($this->getRoute() . '.details')->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        }
    }
}
