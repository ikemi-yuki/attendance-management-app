<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use App\Services\DateService;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\DB;

class AdminAttendanceService
{
    public function __construct(
    private DateService $dateService,
    private AttendanceService $attendanceService
    ) {}

    private function formatSeconds(?int $seconds): string
    {
        if ($seconds === null) {
            return '';
        }
        return CarbonInterval::seconds($seconds)
            ->cascade()
            ->format('%H:%I');
    }

    public function attendanceUpdate(int $attendanceId, array $data)
    {
        return DB::transaction(function () use ($attendanceId, $data) {

            $attendance = Attendance::findOrFail($attendanceId);
            $date = $attendance->work_date;

            $attendance->update([
                'clock_in' => $date->copy()->setTimeFromTimeString($data['clock_in']),
                'clock_out' => $date->copy()->setTimeFromTimeString($data['clock_out']),
                'note' => $data['note'],
            ]);

            foreach ($data['breaks'] as $breakId => $break) {
                if ($breakId === 'new') {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        $attendance->breaks()->create([
                            'break_start' => $date->copy()->setTimeFromTimeString($break['break_start']),
                            'break_end' => $date->copy()->setTimeFromTimeString($break['break_end']),
                        ]);
                    }
                } else {
                    $attendance->breaks()
                        ->where('id', $breakId)
                        ->update([
                            'break_start' => $break['break_start']
                                ? $date->copy()->setTimeFromTimeString($break['break_start'])
                                : null,
                            'break_end' => $break['break_end']
                                ? $date->copy()->setTimeFromTimeString($break['break_end'])
                                : null,
                        ]);
                }
            }
            return $attendance;
        });
    }

    public function getMonthlyCsvData($userId, Carbon $start, Carbon $end): array
    {
        $attendances = $this->attendanceService
        ->getMonthlyAttendances($userId, $start, $end);

        $dates = $this->dateService->getDatesInMonth($start, $end);
        return $dates->map(function ($date) use ($attendances) {
        $attendance = $attendances[$date->toDateString()] ?? null;

            return [
                $date->isoFormat('MM/DD(ddd)'),
                optional($attendance?->clock_in)->format('H:i'),
                optional($attendance?->clock_out)->format('H:i'),
                $this->formatSeconds($attendance?->total_break_seconds),
                $this->formatSeconds($attendance?->work_seconds),
            ];
        })->toArray();
    }
}