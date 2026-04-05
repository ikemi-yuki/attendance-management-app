<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\StaffAttendanceController as AdminStaffAttendanceController;
use App\Http\Controllers\Admin\AttendanceRequestApprovalController as AdminAttendanceRequestApprovalController;
use App\Http\Controllers\User\ClockController as UserClockController;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\User\BreakController as UserBreakController;
use App\Http\Controllers\User\AttendanceRequestController as UserAttendanceRequestController;
use App\Http\Controllers\StampCorrectionRequestController;


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

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])
    ->middleware('guest:admin')
    ->name('admin.login');

    Route::post('/login', [AdminLoginController::class, 'store'])
        ->middleware('guest:admin');

    Route::post('/logout', [AdminLoginController::class, 'destroy'])
        ->middleware('auth:admin')
        ->name('admin.logout');
});

Route::middleware(['auth', 'verified.user'])
    ->prefix('attendance')
    ->group(function () {
        Route::get('/', [UserClockController::class, 'show'])->name('clock');

        Route::post('/clock-in', [UserClockController::class, 'clockIn'])->name('attendance.clockIn');

        Route::post('/clock-out', [UserClockController::class, 'clockOut'])->name('attendance.clockOut');

        Route::post('/break-start', [UserBreakController::class, 'breakStart'])->name('attendance.breakStart');

        Route::post('/break-end', [UserBreakController::class, 'breakEnd'])->name('attendance.breakEnd');

        Route::get('/list', [UserAttendanceController::class, 'index'])->name('attendance.index');

        Route::get('/detail/{id}', [UserAttendanceController::class, 'show'])->name('attendance.show');

        Route::post('/detail/{id}', [UserAttendanceRequestController::class, 'apply'])->name('attendance.apply');
});

Route::middleware(['auth:web,admin','identify.role', 'verified.user'])
    ->get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('request.list');

Route::middleware(['auth:admin','admin'])
    ->group(function () {
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestApprovalController::class, 'show'])->name('admin.request.show');

        Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestApprovalController::class, 'approve'])->name('admin.request.approve');
});

Route::middleware(['auth:admin','admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/attendance/list', [AdminStaffAttendanceController::class, 'index'])->name('admin.attendance.index');

        Route::get('/attendance/{id}', [AdminStaffAttendanceController::class, 'show'])->name('admin.attendance.show');

        Route::patch('/attendance/{id}', [AdminStaffAttendanceController::class, 'update'])->name('admin.attendance.update');

        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.index');

        Route::get('/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'userIndex'])->name('admin.attendance.monthly');

        Route::get('/attendance/staff/export/{id}', [AdminStaffAttendanceController::class, 'export'])->name('admin.attendance.export');
});