<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Support\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = collect();

        $baseUser = User::where('email', 'user@example.com')->first();
        if ($baseUser) {
            $users->push($baseUser);
        }

        $newUsers = User::factory()->count(5)->create();
        $users = $users->merge($newUsers);

        foreach ($users as $user) {
            foreach (range(0, 89) as $daysAgo) {
                $date = Carbon::today()->subDays($daysAgo);

                if (rand(1, 100) > 70) {
                    continue;
                }

                $clockIn = $date->copy()->setTime(8, rand(30, 59));
                $clockOut = $date->copy()->setTime(18, rand(0, 59));
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);

                $breakCount = rand(1, 2);

                for ($breakIndex = 0; $breakIndex < $breakCount; $breakIndex++) {
                    $start = $clockIn->copy()->addHours(3 + $breakIndex);
                    $end = $start->copy()->addMinutes(rand(30, 60));

                    AttendanceBreak::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $start,
                        'break_end' => $end,
                    ]);
                }
            }
        }
    }
}
