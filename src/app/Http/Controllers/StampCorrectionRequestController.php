<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceCorrectRequest;
use App\Services\AttendanceRequestService;
use App\ViewModels\AttendanceRequestRowViewModel;

class StampCorrectionRequestController extends Controller
{
    private $attendanceRequestService;

    public function __construct(AttendanceRequestService $attendanceRequestService)
    {
        $this->attendanceRequestService = $attendanceRequestService;
    }

    public function index(Request $request)
    {
        $role = $request->attributes->get('role');

        $status = $request->query('status', 'pending');

        $requests = $this->attendanceRequestService->getRequests(
            $role,
            $status,
            auth()->id()
        );

        $rows = $requests->map(
                fn ($attendanceRequest) => new AttendanceRequestRowViewModel($attendanceRequest)
        );

        $view = $role === User::ROLE_ADMIN
            ? 'admin.requests.index'
            : 'user.requests.index';

        return view($view, compact('rows','status'));
    }
}
