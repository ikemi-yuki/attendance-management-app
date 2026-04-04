<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-1 month', 'now');

        $clockIn = Carbon::instance($date)->setTime(9, 0);
        $clockOut = Carbon::instance($date)->setTime(18, 0);

        return [
            'user_id' => null,
            'work_date' => $clockIn->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
