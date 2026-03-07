<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DateService;
use App\Services\AttendanceService;
use App\ViewModels\AdminAttendanceRowViewModel;

class StaffAttendanceController extends Controller
{
    private DateService $dateService;
    private AttendanceService $attendanceService;

    public function __construct(
        DateService $dateService,
        AttendanceService $attendanceService
    ) {
        $this->dateService = $dateService;
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $date = $this->dateService->resolveDate($request->query('date'));

        $dates = $this->dateService->getPreviousNextDates($date);

        $attendances = $this->attendanceService->getAttendancesByDate($date);

        $rows = $attendances->map(
            fn ($attendance) => (new AdminAttendanceRowViewModel($attendance))->toArray()
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

    public function show()
    {
        view('admin.attendances.show');
    }
}
