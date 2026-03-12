<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\DateService;
use App\Services\AttendanceService;
use App\Services\AttendanceRequestService;
use App\ViewModels\MonthlyAttendanceRowViewModel;

class AttendanceController extends Controller
{
    private DateService $dateService;
    private AttendanceService $attendanceService;
    private AttendanceRequestService $attendanceRequestService;

    public function __construct(
        DateService $dateService,
        AttendanceService $attendanceService,
        AttendanceRequestService $attendanceRequestService
    ) {
        $this->dateService = $dateService;
        $this->attendanceService = $attendanceService;
        $this->attendanceRequestService = $attendanceRequestService;
    }

    public function clockIn()
    {
        $this->attendanceService->clockIn();

        return redirect()->route('clock');
    }

    public function clockOut()
    {
        $this->attendanceService->clockOut();

        return redirect()->route('clock');
    }

    public function index(Request $request)
    {
        $month = $this->dateService->resolveMonth($request->month);

        $months = $this->dateService->getPreviousNextMonths($month);

        [$start, $end] = $this->dateService->getMonthRange($month);

        $dates = $this->dateService->getDatesInMonth($start, $end);

        $attendances = $this->attendanceService->getMonthlyAttendances(Auth::id(), $start, $end);

        $rows = collect($dates)->map(
            fn ($date) => new MonthlyAttendanceRowViewModel(
                $date,
                $attendances[$date->toDateString()] ?? null
            )
        );

        return view('user.attendances.index', [
            'month' => $month,
            'previousUrl' => route('attendance.index', [
                'month' => $months['previous']->format('Y-m')
            ]),
            'nextUrl' => route('attendance.index', [
                'month' => $months['next']->format('Y-m')
            ]),
            'rows' => $rows,
        ]);
    }

    public function show($id)
    {
        $attendance = $this->attendanceService->getAttendanceDetail($id);

        $attendanceRequest = $this->attendanceRequestService->getAttendanceRequestDetail($id);

        $hasPendingRequest = $attendance->pendingRequest()->exists();

        return view('user.attendances.show', compact(
            'attendance',
            'attendanceRequest',
            'hasPendingRequest'
        ));
    }
}
