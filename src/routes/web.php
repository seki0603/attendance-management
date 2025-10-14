<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminCorrectionController;
use App\Http\Controllers\StaffController;

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

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register/store', [AuthController::class, 'store'])->name('register.store');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');

Route::middleware(['auth'])->group(function () {
    Route::get('email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/page', [VerificationController::class, 'page'])->name('verification.site');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed'])->name('verification.verify');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/list', [AttendanceController::class, 'showList'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');

    Route::post('/attendance/detail/{attendance}', [CorrectionController::class, 'store'])->name('correction.store');
    Route::get('/stamp_correction_request/list', [CorrectionController::class, 'showList'])->name('correction.list');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');
    Route::get('attendance/{id}', [AttendanceController::class, 'showDetail'])->name('admin.attendance.detail');
    Route::put('attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/stamp_correction_request/list', [AdminCorrectionController::class, 'index'])->name('admin.correction.list');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionController::class, 'showApproveForm'])->name('admin.correction.approve');
    Route::put('/stamp_correction_request/approve/{id}', [AdminCorrectionController::class, 'update'])->name('admin.correction.approve.update');

    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');
    Route::get('/attendance/staff/{id}', [StaffController::class, 'showAttendanceList'])->name('admin.attendance.staff.list');

    Route::get('/attendance/staff/{id}/csv', [StaffController::class, 'exportCsv'])->name('admin.attendance.staff.csv');
});