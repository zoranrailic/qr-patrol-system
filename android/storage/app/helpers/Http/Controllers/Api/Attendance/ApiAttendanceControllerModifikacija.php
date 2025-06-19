<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

class ApiAttendanceController extends Controller
{
    /**
     * Save attendance data via API
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiSaveAttendance(Request $request)
    {
        // Get all request data
        $new = $request->all();

        // Get settings data
        $getSetting = Setting::find(1);

        // Extract data from the request
        $key = $new['key'] ?? null;
        $q = $new['q'] ?? null;
        $location = $new['location'] ?? null;

        // Decrypt the QR ID
        try {
            $qrId = Crypt::decryptString($new['qr_id']);
        } catch (DecryptException $e) {
            Log::error($e);
            return response()->json(['message' => 'Error Qr!'], 200);
        }

        // Get the current date and time based on timezone
        $date = Carbon::now()->timezone($getSetting->timezone)->format('Y-m-d');
        $currentTime = Carbon::now()->timezone($getSetting->timezone)->format('H:i:s');

        // Validate the key
        if (empty($key) || $key !== $getSetting->key_app) {
            return response()->json(['message' => 'The KEY is Wrong!'], 200);
        }

        // Handle Check-in
        if ($q === 'in') {
            // Check if already checked in
            $checkAlreadyCheckIn = Attendance::where('worker_id', $qrId)
                ->where('date', $date)
                ->whereNotNull('in_time')
                ->whereNull('out_time')
                ->first();

            if ($checkAlreadyCheckIn) {
                return response()->json(['message' => 'already check-in'], 200);
            }

            // Calculate late time
            $in_time = new Carbon($currentTime);
            $startHour = Carbon::createFromFormat('H:i:s', $getSetting->start_time);
            $lateTime = $in_time->greaterThan($startHour) ? $in_time->diff($startHour)->format('%H:%I:%S') : '00:00:00';

            // Save attendance data
            $attendance = new Attendance();
            $attendance->worker_id = $qrId;
            $attendance->date = $date;
            $attendance->in_location = $location;
            $attendance->out_location = $location; // Automatically set out_location
            $attendance->in_time = $currentTime;
            $attendance->late_time = $lateTime;

            if ($attendance->save()) {
                return response()->json([
                    'message' => 'Success!',
                    'date' => $date,
                    'time' => $currentTime,
                    'location' => $location,
                    'query' => 'Check-in',
                ], 200);
            }

            return response()->json(['message' => 'Error! Something Went Wrong!'], 200);
        }

        // Handle Check-out
        if ($q === 'out') {
            $out_time = new Carbon($currentTime);
            $getOutHour = new Carbon($getSetting->out_time);

            // Get in-time record from the database
            $getInTime = Attendance::where('worker_id', $qrId)
                ->where('date', $date)
                ->whereNull('out_time')
                ->first();

            if (!$getInTime) {
                return response()->json(['message' => 'check-in first'], 200);
            }

            $in_time = new Carbon($getInTime->in_time);
            $workHour = $out_time->diff($in_time)->format('%H:%I:%S');
            $overTime = $out_time->greaterThan($getOutHour) ? $out_time->diff($getOutHour)->format('%H:%I:%S') : '00:00:00';
            $earlyOutTime = $out_time->lessThan($getOutHour) ? $getOutHour->diff($out_time)->format('%H:%I:%S') : '00:00:00';

            // Update attendance data
            $getInTime->out_time = $currentTime;
            $getInTime->out_location = $getInTime->in_location; // Set out_location same as in_location
            $getInTime->work_hour = $workHour;
            $getInTime->over_time = $overTime;
            $getInTime->early_out_time = $earlyOutTime;

            if ($getInTime->save()) {
                return response()->json([
                    'message' => 'Success!',
                    'date' => $date,
                    'time' => $currentTime,
                    'location' => $location,
                    'query' => 'Check-Out',
                ], 200);
            }

            return response()->json(['message' => 'Error! Something Went Wrong!'], 200);
        }

        return response()->json(['message' => 'Error! Wrong Command!'], 200);
    }
}
