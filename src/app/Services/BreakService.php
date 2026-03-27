<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BreakService
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function start(User $user)
    {
        $attendance = $this->attendanceService->getTodayAttendance($user);

        if (!$attendance) {
            abort(400, '出勤していません');
        }

        $onBreak = $attendance->breaks()->whereNull('break_end')->exists();
        if ($onBreak) {
            abort(400, 'すでに休憩中です');
        }

        return $attendance->breaks()->create([
            'break_start' => Carbon::now(),
        ]);
    }

    public function end(User $user)
    {
        $attendance = $this->attendanceService->getTodayAttendance($user);

        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->latest()
            ->first();

        if (!$break) {
            abort(400, '休憩を開始していません');
        }

        $break->update([
            'break_end' => Carbon::now(),
        ]);

        return $break;
    }
}