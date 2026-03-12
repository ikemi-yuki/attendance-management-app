<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Services\DateService;
use App\Services\AttendanceService;
use App\Services\AttendanceRequestService;
use App\Services\AdminAttendanceService;
use App\ViewModels\AdminAttendanceRowViewModel;

class StaffAttendanceController extends Controller
{
    private DateService $dateService;
    private AttendanceService $attendanceService;
    private AttendanceRequestService $attendanceRequestService;
    private AdminAttendanceService $adminAttendanceService;

    public function __construct(
        DateService $dateService,
        AttendanceService $attendanceService,
        AttendanceRequestService $attendanceRequestService,
        AdminAttendanceService $adminAttendanceService
    ) {
        $this->dateService = $dateService;
        $this->attendanceService = $attendanceService;
        $this->attendanceRequestService = $attendanceRequestService;
        $this->adminAttendanceService = $adminAttendanceService;
    }

    public function index(Request $request)
    {
        $date = $this->dateService->resolveDate($request->query('date'));

        $dates = $this->dateService->getPreviousNextDates($date);

        $attendances = $this->attendanceService->getAttendancesByDate($date);

        $rows = $attendances->map(
            fn ($attendance) => new AdminAttendanceRowViewModel($attendance)
        );

        return view('admin.attendances.index', [
            'date' => $date,
            'previousUrl' => route('admin.attendance.index', [
                'date' => $dates['previous']->toDateString()
            ]),
            'nextUrl' => route('admin.attendance.index', [
                'date' => $dates['next']->toDateString()
            ]),
            'rows' => $rows,
        ]);
    }

    public function show($id)
    {
        $attendance = $this->attendanceService->getAttendanceDetail($id);

        $attendanceRequest = $this->attendanceRequestService->getAttendanceRequestDetail($id);

        $hasPendingRequest = $attendance->pendingRequest()->exists();

        return view('admin.attendances.show', compact(
            'attendance',
            'attendanceRequest',
            'hasPendingRequest'));
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $this->adminAttendanceService->attendanceUpdate($id, $request->validated());
        return redirect()->route('admin.attendance.show', $id);
    }
}
