<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Services\DateService;
use App\Services\AttendanceService;
use App\Services\AttendanceRequestService;
use App\Services\AdminAttendanceService;
use App\ViewModels\AdminAttendanceRowViewModel;
use App\ViewModels\MonthlyAttendanceRowViewModel;

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
            'hasPendingRequest'
        ));
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $this->adminAttendanceService->attendanceUpdate($id, $request->validated());
        return redirect()->route('admin.attendance.show', $id);
    }

    public function userIndex(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $this->dateService->resolveMonth($request->month);

        $months = $this->dateService->getPreviousNextMonths($month);

        [$start, $end] = $this->dateService->getMonthRange($month);

        $dates = $this->dateService->getDatesInMonth($start, $end);

        $attendances = $this->attendanceService->getMonthlyAttendances($id, $start, $end);

        $rows = $dates->map(
            fn ($date) => new MonthlyAttendanceRowViewModel(
                $date,
                $attendances[$date->toDateString()] ?? null
            )
        );

        return view('admin.attendances.staff_index', [
            'user' => $user,
            'month' => $month,
            'previousUrl' => route('admin.attendance.monthly', [
                'id' => $id,
                'month' => $months['previous']->format('Y-m')
            ]),
            'nextUrl' => route('admin.attendance.monthly', [
                'id' => $id,
                'month' => $months['next']->format('Y-m')
            ]),
            'rows' => $rows,
        ]);
    }

    public function export(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $this->dateService->resolveMonth($request->month);

        [$start, $end] = $this->dateService->getMonthRange($month);

        $csvData = $this->adminAttendanceService
        ->getMonthlyCsvData($id, $start, $end);

        $fileName = 'attendance_' . $user->name . '_' . $month->isoFormat('Y_MM') . '.csv';

        return response()->streamDownload(function () use ($csvData) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
