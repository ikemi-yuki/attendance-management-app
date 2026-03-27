<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = now()->copy()->setTime(12, 0);
        $end = now()->copy()->setTime(13, 0);

        return [
            'attendance_id' => null,
            'break_start' => $start,
            'break_end' => $end,
        ];
    }
}
