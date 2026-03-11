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

    public function getStatus()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            return 'before';
        }

        if ($attendance->clock_out) {
            return 'finished';
        }

        $onBreak = $attendance->breaks
            ->whereNull('break_end')
            ->isNotEmpty();

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

    public function getAttendancesByDate(Carbon $date)
    {
        return Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $date)
            ->get();
    }

    public function getMonthlyAttendances($userId, Carbon $start, Carbon $end)
    {
        return Attendance::where('user_id', $userId)
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy(fn ($attendance) =>
                $attendance->work_date->toDateString()
            );
    }

    public function getAttendanceDetail(int $attendanceId)
    {
        return Attendance::with([
            'user',
            'breaks'
        ])->findOrFail($attendanceId);
    }
}