<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BreakService
{
    public function start()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', Carbon::today())
            ->firstOrFail();

        return $attendance->breaks()->create([
            'break_start' => Carbon::now(),
        ]);
    }

    public function end()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('work_date', Carbon::today())
            ->firstOrFail();

        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->latest()
            ->firstOrFail();

        $break->update([
            'break_end' => Carbon::now(),
        ]);

        return $break;
    }
}