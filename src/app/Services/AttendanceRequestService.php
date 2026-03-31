<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceRequestService
{
    public function store(array $data, Attendance $attendance)
    {
        return DB::transaction(function () use ($data, $attendance) {

            $date = $attendance->work_date;

            $attendanceRequest = AttendanceCorrectRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'requested_clock_in' => $date->copy()->setTimeFromTimeString($data['clock_in']),
                'requested_clock_out' => $date->copy()->setTimeFromTimeString($data['clock_out']),
                'requested_note' => $data['note'],
                'status' => AttendanceCorrectRequest::STATUS_PENDING,
                'requested_at' => now(),
            ]);

            if (!empty($data['breaks'])) {

                foreach ($data['breaks'] as $key => $break) {

                    if (empty($break['break_start']) || empty($break['break_end'])) {
                        continue;
                    }

                    $attendanceRequest->requestBreaks()->create([
                        'attendance_break_id' => $key === 'new' ? null : $key,
                        'requested_break_start' => $date->copy()->setTimeFromTimeString($break['break_start']),
                        'requested_break_end' => $date->copy()->setTimeFromTimeString($break['break_end']),
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

    public function approve(int $requestId, int $adminId): void
    {
        DB::transaction(function () use ($requestId, $adminId) {

            $request = AttendanceCorrectRequest::with([
                'attendance',
                'requestBreaks'
            ])->findOrFail($requestId);

            if ($request->status !== AttendanceCorrectRequest::STATUS_PENDING) {
                throw new \Exception('すでに承認済み');
            }

            $attendance = $request->attendance;

            $attendance->update([
                'clock_in' => $request->requested_clock_in,
                'clock_out' => $request->requested_clock_out,
                'note' => $request->requested_note,
            ]);

            foreach ($request->requestBreaks as $requestBreak) {

                if ($requestBreak->attendance_break_id) {
                    $requestBreak->attendanceBreak->update([
                        'break_start' => $requestBreak->requested_break_start,
                        'break_end' => $requestBreak->requested_break_end,
                    ]);
                } else {
                    $attendance->breaks()->create([
                        'break_start' => $requestBreak->requested_break_start,
                        'break_end' => $requestBreak->requested_break_end,
                    ]);
                }
            }

            $request->update([
                'status' => AttendanceCorrectRequest::STATUS_APPROVED,
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);
        });
    }
}