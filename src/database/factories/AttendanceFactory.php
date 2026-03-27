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

    public function today()
    {
        return $this->state(function () {
            return [
                'work_date' => Carbon::today()->toDateString(),
                'clock_in' => Carbon::today()->setTime(9, 0),
                'clock_out' => Carbon::today()->setTime(18, 0),
            ];
        });
    }
}
