<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = Carbon::today()->setTime(12, 0);
        $end = Carbon::today()->setTime(13, 0);

        return [
            'attendance_id' => null,
            'break_start' => $start,
            'break_end' => $end,
        ];
    }
}
