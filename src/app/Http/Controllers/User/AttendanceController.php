<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    private $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function clockIn()
    {
        $this->attendanceService->clockIn();

        return redirect()->route('clock');
    }

    public function clockOut()
    {
        $this->attendanceService->clockOut();

        return redirect()->route('clock');
    }
}
