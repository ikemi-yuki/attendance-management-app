<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceRequestService;
use App\Models\AttendanceCorrectRequest;

class AttendanceRequestApprovalController extends Controller
{
    private $service;

    public function __construct(AttendanceRequestService $service)
    {
        $this->service = $service;
    }

    public function show($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceCorrectRequest::with([
            'user',
            'attendance',
            'requestBreaks'
        ])->findOrFail($attendance_correct_request_id);

        $hasPendingRequest = $attendanceRequest->status === AttendanceCorrectRequest::STATUS_PENDING;

        return view('admin.requests.show', compact(
            'attendanceRequest',
            'hasPendingRequest'
        ));
    }

    public function approve($attendance_correct_request_id)
    {
        $this->service->approve(
            $attendance_correct_request_id,
            auth()->id()
        );

        return redirect()->route('admin.request.show', $attendance_correct_request_id);
    }
}
