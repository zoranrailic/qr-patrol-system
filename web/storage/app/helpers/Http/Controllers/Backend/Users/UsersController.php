<?php

namespace App\Http\Controllers\Backend\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use App\Models\History;
use App\Models\Role;
use App\Models\User;
use Auth;
use Config;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Exception;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Summary of index
     * @param \Yajra\Datatables\Datatables $datatables
     * @return mixed
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'image',
            'name',
            'email',
            'role',
            'created_at',
            'updated_at',
            'action' => ['orderable' => false, 'searchable' => false],
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(User::with('roles')->get())  // Assuming 'roles' is the correct relationship name
                ->addColumn('image', function (User $data) {
                    $getAssetFolder = asset('uploads/' . $data->image);
                    return '<img src="' . $getAssetFolder . '" width="30px" class="img-circle elevation-2">';
                })
                ->addColumn('action', function (User $data) {
                    $routeEdit = route($this->getRoute() . '.edit', $data->id);
                    $routeDelete = route($this->getRoute() . '.delete', $data->id);

                    // Zo! Provera da li je korisnik administrator
                    if (Auth::user()->hasRole('administrator')) {
                        $button = '<a href="' . $routeEdit . '"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="' . $routeDelete . '" class="delete-button"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a>';
                    } else {
                        $button = '<a href="#"><button class="btn btn-primary disabled" style="pointer-events: none;" title="Nemate dozvolu"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="#"><button class="btn btn-danger disabled" style="pointer-events: none;" title="Nemate dozvolu"><i class="fa fa-trash"></i></button></a>';
                    }

                    return $button;
                })
                ->addColumn('role', function (User $user) {
                    return $user->roles->isNotEmpty() ? $user->roles->first()->display_name : 'No Role Assigned';  // Assuming 'roles' returns a collection
                })
                ->rawColumns(['action', 'image', 'role'])
                ->toJson();
        }

        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [10, 25, 50, -1],
                    ['10 rows', '25 rows', '50 rows', 'Show all'],
                ],
                'dom' => 'Bfrtip',
                'buttons' => ['pageLength', 'csv', 'excel', 'pdf', 'print'],
            ]);

        return view('backend.users.index', compact('html'));
    }

    /**
     * Summary of add
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function add()
    {
        $data = new User();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        $roles = $this->getRolesForForm();

        return view('backend.users.form', [
            'data' => $data,
            'role' => $roles,
        ]);
    }

    /**
     * Summary of getRoute
     * @return string
     */
    private function getRoute()
    {
        return 'users';
    }

    /**
     * Summary of create
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $new = $request->all();
        $this->validator($new, 'create')->validate();
        try {
            $new['password'] = bcrypt($new['password']);
            $createNew = User::create($new);
            if ($createNew) {
                // Attach role
                $createNew->roles()->attach($new['role']);

                // upload image
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    // image file name example: [news_id]_image.jpg
                    ${'image'} = $createNew->id . "_image." . $file->getClientOriginalExtension();
                    // save image to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                    $createNew->{'image'} = ${'image'};
                } else {
                    $createNew->{'image'} = 'default-user.png';
                }

                // If user create with 2 or 3 means admin or staff, will save the QR history also
                if ($new['role'] == 2 || $new['role'] == 3) {
                    History::create($new);

                    // Save log
                    $controller = new SaveActivityLogController();
                    $controller->saveLog($new, "Create new history QR");
                }

                // Save user
                $createNew->save();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Create new user");

                // Create is successful, back to list
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }

            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

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
            'email' => $type == 'create' ? 'email|required|string|max:255|unique:users' : 'required|string|max:255|unique:users,email,' . $data['id'],
            // (update: not required, create: required)
            'password' => $type == 'create' ? 'required|string|min:6|max:255' : '',
            'name' => $this->validName($type, $data),
        ]);
    }

    /**
     * Summary of validName
     * @param mixed $type
     * @param mixed $data
     * @return string
     */
    public function validName($type, $data)
    {
        $rules = 'required|string|max:255';

        if ($type == 'create') {
            // For 'create', ensure the name is unique in the 'histories' table
            $rules .= '|unique:histories,name';
        } else {
            // For 'update', ensure the name is unique in the 'histories' table but ignore the current user's history ID
            // Check if the user has 1 or 4 roles
            if ($data['old_role'] == 1 || $data['old_role'] == 4) {
                $rules .= '';
            } else {
                $rules .= '|unique:histories,name,' . $data['qr_id'];
            }
        }

        return $rules;
    }

    /**
     * Summary of edit
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $data = User::find($id);
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Edit';

        // Get history id by name
        $getHistory = History::where('name', $data->name)
            ->first();
        $getHistory ? $data->qr_id = $getHistory->id : 0;

        $roles = $this->getRolesForForm();

        return view('backend.users.form', [
            'data' => $data,
            'role' => $roles,
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
            $new['old_role'] = $currentData->role;

            if ($currentData) {
                $this->validator($new, 'update')->validate();

                if (!$new['password']) {
                    $new['password'] = $currentData['password'];
                } else {
                    $new['password'] = bcrypt($new['password']);
                }

                // Check if the name has changed
                if ($currentData->name != $new['name']) {
                    // Update the name in the histories table
                    $historyRecord = History::where('name', $currentData->name)->first();
                    if ($historyRecord) {
                        $historyRecord->name = $new['name'];
                        $historyRecord->save();
                    }
                }

                if ($currentData->role != $new['role']) {
                    $currentData->roles()->detach();
                    $currentData->roles()->attach($new['role']);
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

                // If user update role 2 or 3 means admin or staff will upadate also the history QR
                if ($request->has('qr_id') && ($new['role'] == 2 || $new['role'] == 3)) {
                    $currentDataHistory = History::find($request->get('qr_id'));

                    if ($currentDataHistory) {
                        // Update
                        $currentDataHistory->update($new);

                        // Save log
                        $controller = new SaveActivityLogController();
                        $controller->saveLog($new, "Update history QR");
                    }
                }

                // Update
                $currentData->update($new);

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update user");

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    /**
     * Summary of delete
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        try {
            if (Auth::user()->id != $id) {
                $user = User::find($id);
                if (!$user) {
                    return redirect()->route($this->getRoute())->with('error', 'User not found.');
                }

                // Delete the QR code
                $history = History::where('name', $user->name)->first();
                if ($history) {
                    $history->delete();
                }

                $user->roles()->detach();

                if ($user->{'image'} != 'default-user.png') {
                    $filePath = Config::get('const.UPLOAD_PATH') . $user['image'];
                    if (File::exists($filePath)) {
                        File::delete($filePath);
                    }
                }

                $user->delete();

                $controller = new SaveActivityLogController();
                $controller->saveLog($user->toArray(), "Delete user");

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
            }
            return redirect()->route($this->getRoute())->with('error', 'You cannot delete your own account.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', 'Failed to delete user due to database constraints.');
        } catch (\Exception $e) {
            return redirect()->route($this->getRoute())->with('error', 'An unexpected error occurred while deleting the user.');
        }
    }

    /**
     * Get roles for form
     *
     * @return mixed
     */
    private function getRolesForForm()
    {
        if (Auth::user()->hasRole('administrator')) {
            return Role::orderBy('id')->pluck('display_name', 'id');
        }

        return Role::whereNotIn('id', [1, 2])->orderBy('id')->pluck('display_name', 'id');
    }
}
