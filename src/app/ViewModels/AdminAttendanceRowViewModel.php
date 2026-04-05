<?php

namespace App\ViewModels;

use App\Models\Attendance;
use Carbon\CarbonInterval;

class AdminAttendanceRowViewModel
{
    public function __construct(
        private Attendance $attendance
    ) {}

    private function formatSeconds(?int $seconds): string
    {
        if (!$seconds) {
            return '';
        }

        return CarbonInterval::seconds($seconds)
            ->cascade()
            ->format('%H:%I');
    }

    public function name(): string
    {
        return $this->attendance->user->name;
    }

    public function clockIn(): string
    {
        return optional($this->attendance->clock_in)->format('H:i') ?? '';
    }

    public function clockOut(): string
    {
        return optional($this->attendance->clock_out)->format('H:i') ?? '';
    }

    public function breakTime(): string
    {
        return $this->formatSeconds($this->attendance->total_break_seconds);
    }

    public function workTime(): string
    {
        return $this->formatSeconds($this->attendance->work_seconds);
    }

    public function detailUrl(): string
    {
        return route('admin.attendance.show', $this->attendance->id);
    }
}