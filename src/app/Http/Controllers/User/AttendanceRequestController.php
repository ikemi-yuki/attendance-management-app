<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceRequestService;

class AttendanceRequestController extends Controller
{
    private $service;

    public function __construct(AttendanceRequestService $service)
    {
        $this->service = $service;
    }

    public function store(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $this->service->store(
            $request->validated(),
            $attendance
        );

        $hasPendingRequest = $attendance->pendingRequest();

        return redirect()
            ->route('request.list', ['status' => 'pending']);
    }
}
