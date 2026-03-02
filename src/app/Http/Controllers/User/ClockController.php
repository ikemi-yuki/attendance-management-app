<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceService;

class ClockController extends Controller
{
    private $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function show()
    {
        $attendance = $this->attendanceService->getTodayAttendance();

        $status = $this->attendanceService->getStatus($attendance);

        return view('user.attendances.clock', compact(
            'attendance',
            'status'
        ));
    }
}
