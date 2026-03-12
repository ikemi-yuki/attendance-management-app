<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrectRequest;
use App\ViewModels\AttendanceRequestRowViewModel;

class AttendanceRequestApprovalController extends Controller
{
    public function show($attendance_correct_request_id)
    {
        $attendanceRequest = AttendanceCorrectRequest::with([
            'user',
            'attendance',
            'requestBreaks'
        ]);

        $hasPendingRequest = $attendance->pendingRequest()->exists();

        $hasApprovedRequest = $attendance->approvedRequest()->exists();

        return view('admin.requests.show', compact(
            'attendanceRequest',
            'hasPendingRequest',
            'hasApprovedRequest'));
    }
}
