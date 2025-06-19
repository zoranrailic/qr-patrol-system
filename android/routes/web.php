<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Backend\Profile\ProfileController;
use App\Http\Controllers\Backend\Attendance\AttendanceController;
use App\Http\Controllers\Backend\History\HistoryController;
use App\Http\Controllers\Backend\QrCode\QrCodeController;
use App\Http\Controllers\Utils\Activity\ReinputKeyController;
use App\Http\Controllers\Backend\Users\UsersController;
use App\Http\Controllers\Backend\Setting\SettingsController;
use App\Http\Controllers\Backend\Analytic\AnalyticController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| administrator
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator']], function () {
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/add', [UsersController::class, 'add'])->name('users.add');
    Route::post('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::get('/users/edit/{id}', [UsersController::class, 'edit'])->name('users.edit');
    Route::post('/users/update', [UsersController::class, 'update'])->name('users.update');
    Route::get('/users/delete/{id}', [UsersController::class, 'delete'])->name('users.delete');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/downloadSettingsQrCode', [SettingsController::class, 'downloadSettingsQrCode'])->name('settings.downloadSettingsQrCode');

    Route::get('/analytic', [AnalyticController::class, 'index'])->name('analytic');
});

/*
|--------------------------------------------------------------------------
| administrator|admin|editor|guest
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|staff|guest']], function () {
    Route::get('/checkProductVerify', [MainController::class, 'checkProductVerify'])->name('checkProductVerify');

    Route::get('/profile/details', [ProfileController::class, 'details'])->name('profile.details');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});


/*
|--------------------------------------------------------------------------
| administrator|admin|staff
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin|staff']], function () {
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances');
});

/*
|--------------------------------------------------------------------------
| administrator|admin
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['role:administrator|admin']], function () {
    Route::get('/histories', [HistoryController::class, 'index'])->name('histories');
    Route::get('/histories/add', [HistoryController::class, 'add'])->name('histories.add');
    Route::post('/histories/create', [HistoryController::class, 'create'])->name('histories.create');
    Route::get('/histories/edit/{id}', [HistoryController::class, 'edit'])->name('histories.edit');
    Route::post('/histories/update', [HistoryController::class, 'update'])->name('histories.update');
    Route::get('/histories/delete/{id}', [HistoryController::class, 'delete'])->name('histories.delete');

    Route::get('/histories/import', [HistoryController::class, 'import'])->name('histories.import');
    Route::post('/histories/importData', [HistoryController::class, 'importData'])->name('histories.importData');
    Route::get('/histories/downloadQrCode/{dataQr}/{name}', [QrCodeController::class, 'downloadQrCode'])->name('histories.downloadQrCode');
});

Route::post('reinputkey/index/{code}', [ReinputKeyController::class, 'index']);
