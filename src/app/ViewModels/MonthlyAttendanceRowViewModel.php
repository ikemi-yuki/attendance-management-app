<?php

namespace App\ViewModels;

use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class MonthlyAttendanceRowViewModel
{
    public function __construct(
        private Carbon $date,
        private ?Attendance $attendance
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

    public function date(): string
    {
        return $this->date->isoFormat('MM/DD(ddd)');
    }

    public function clockIn(): string
    {
        return optional($this->attendance?->clock_in)->format('H:i') ?? '';
    }

    public function clockOut(): string
    {
        return optional($this->attendance?->clock_out)->format('H:i') ?? '';
    }

    public function breakTime(): string
    {
        return $this->formatSeconds(
            $this->attendance?->total_break_seconds
        );
    }

    public function workTime(): string
    {
        return $this->formatSeconds(
            $this->attendance?->work_seconds
        );
    }

    public function detailUrl(): ?string
    {
        return $this->attendance
                ? route('attendance.show', $this->attendance->id)
                : null;
    }

    public function adminDetailUrl(): ?string
    {
        return $this->attendance
                ? route('admin.attendance.show', $this->attendance->id)
                : null;
    }
}