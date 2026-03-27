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
        $status = $this->attendanceService->getStatus(auth()->user());

        return view('user.attendances.clock', compact('status'));
    }
}
