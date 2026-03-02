<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceService
{
    public function getTodayAttendance()
    {
        return Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereDate('work_date', Carbon::today())
            ->first();
    }

    public function getStatus($attendance)
    {
        if (!$attendance) {
            return 'before';
        }

        if ($attendance->clock_out) {
            return 'finished';
        }

        $onBreak = $attendance->breaks()
            ->whereNull('break_end')
            ->exists();

        return $onBreak ? 'on_break' : 'working';
    }

    public function clockIn()
    {
        return Attendance::create([
            'user_id' => Auth::id(),
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::now(),
        ]);
    }

    public function clockOut()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            abort(400, '出勤していません');
        }

        if ($attendance->clock_out) {
            abort(400, 'すでに退勤済です');
        }

        $attendance->update([
            'clock_out' => Carbon::now(),
        ]);

        return $attendance;
    }
}