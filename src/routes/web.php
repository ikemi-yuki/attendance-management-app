<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\User\ClockController as UserClockController;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\User\BreakController as UserBreakController;
use App\Http\Controllers\Admin\StaffAttendanceController as AdminStaffAttendanceController;

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

Route::middleware(['auth'])
    ->prefix('attendance')
    ->group(function () {
    Route::get('/', [UserClockController::class, 'show'])->name('clock');

    Route::post('/clock-in', [UserAttendanceController::class, 'clockIn'])->name('attendance.clock-in');

    Route::post('/clock-out', [UserAttendanceController::class, 'clockOut'])->name('attendance.clock-out');

    Route::post('/break-start', [UserBreakController::class, 'start'])->name('attendance.break-start');

    Route::post('/break-end', [UserBreakController::class, 'end'])->name('attendance.break-end');
});

Route::middleware(['auth:admin','admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/attendance/list', [AdminStaffAttendanceController::class, 'index'])->name('admin.attendance.list');
});
