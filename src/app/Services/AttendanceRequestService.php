<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceRequestService
{
    public function store(array $data, Attendance $attendance)
    {
        return DB::transaction(function () use ($data, $attendance) {

            $attendanceRequest = AttendanceCorrectRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'requested_clock_in' => $data['clock_in'],
                'requested_clock_out' => $data['clock_out'],
                'requested_note' => $data['note'],
                'status' => AttendanceCorrectRequest::STATUS_PENDING,
                'requested_at' => now(),
            ]);

            if (!empty($data['breaks'])) {

                foreach ($data['breaks'] as $key => $break) {

                    if (empty($break['break_start']) && empty($break['break_end'])) {
                        continue;
                    }

                    $attendanceRequest->requestBreaks()->create([
                        'attendance_break_id' => $key === 'new' ? null : $key,
                        'requested_break_start' => $break['break_start'],
                        'requested_break_end' => $break['break_end'],
                    ]);
                }
            }

            return $attendanceRequest;
        });
    }

    public function getAttendanceRequestDetail(int $attendanceId)
    {
        return AttendanceCorrectRequest::with([
            'user',
            'requestBreaks'
        ])
        ->where('attendance_id', $attendanceId)
        ->where('status', AttendanceCorrectRequest::STATUS_PENDING)
        ->first();
    }

    public function getRequests($role, $status, $userId)
    {
        $statusValue = $status === 'approved'
            ? AttendanceCorrectRequest::STATUS_APPROVED
            : AttendanceCorrectRequest::STATUS_PENDING;

        $query = AttendanceCorrectRequest::with('user','attendance')
            ->where('status', $statusValue);

        if ($role !== User::ROLE_ADMIN) {
            $query->where('user_id', $userId);
        }

        return $query->latest()->get();
    }
}