<?php

namespace App\ViewModels;

use App\Models\AttendanceCorrectRequest;

class AttendanceRequestRowViewModel
{
    public function __construct(
        private AttendanceCorrectRequest $attendanceRequest
    ) {}

    public function status(): string
    {
        return $this->attendanceRequest->status === AttendanceCorrectRequest::STATUS_PENDING
            ? '承認待ち'
            : '承認済み';
    }

    public function name(): string
    {
        return $this->attendanceRequest->user->name;
    }

    public function targetDate(): string
    {
        return $this->attendanceRequest
            ->attendance
            ->work_date
            ->isoFormat('Y/MM/DD');
    }

    public function reason(): string
    {
        return $this->attendanceRequest->requested_note;
    }

    public function requestedAt(): string
    {
        return $this->attendanceRequest
            ->requested_at
            ->isoFormat('Y/MM/DD');
    }

    public function detailUrl(): string
    {
        return route('attendance.show', $this->attendanceRequest->attendance_id);
    }

    public function adminDetailUrl(): string
    {
        return route('admin.request.show', $this->attendanceRequest->attendance_correct_request_id);
    }
}