<?php

namespace App\ViewModels;

use App\Models\Attendance;

class AdminAttendanceRowViewModel
{
    public function __construct(
        private Attendance $attendance
    ) {}

    public function toArray(): array
    {
        return [
            'cells' => [
                $this->attendance->user->name,
                optional($this->attendance->clock_in)->format('H:i') ?? '',
                optional($this->attendance->clock_out)->format('H:i') ?? '',
                gmdate('H:i', $this->attendance->total_break_seconds),
                gmdate('H:i', $this->attendance->work_seconds),
            ],
            'link' => route('admin.attendance.show', $this->attendance->id),
        ];
    }
}