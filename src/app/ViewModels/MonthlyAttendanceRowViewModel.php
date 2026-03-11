<?php

namespace App\ViewModels;

use App\Models\Attendance;
use Carbon\Carbon;

class MonthlyAttendanceRowViewModel
{
    public function __construct(
        private Carbon $date,
        private ?Attendance $attendance
    ) {}

    public function toArray(): array
    {
        return [
            'cells' => [
                $this->date->isoFormat('MM/DD(ddd)'),
                optional($this->attendance?->clock_in)->format('H:i') ?? '',
                optional($this->attendance?->clock_out)->format('H:i') ?? '',
                $this->attendance
                    ? gmdate('H:i', $this->attendance->total_break_seconds)
                    : '',
                $this->attendance
                    ? gmdate('H:i', $this->attendance->work_seconds)
                    : '',
            ],
            'link' => $this->attendance
                ? route('attendance.show', $this->attendance->id)
                : null,
        ];
    }
}