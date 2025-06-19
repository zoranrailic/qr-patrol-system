<?php

use App\Http\Controllers\Api\Attendance\ApiAttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('attendance/apiSaveAttendance', [ApiAttendanceController::class, 'apiSaveAttendance']);

Route::get('/helper/{code}', function ($code) {
    return App\Helpers\Helper::checkingCode($code);
});
Route::get('/helper', function () {
    return App\Helpers\Helper::getInfo();
});
Route::get('/write', function () {
    return App\Helpers\Helper::write();
});
