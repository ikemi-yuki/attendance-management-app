<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClockPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow('2026-04-01 10:00:00');

        $user =User::factory()->create();

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('2026年4月1日(水)');
        $response->assertSee('10:00');
    }

    public function test_勤務外の場合勤怠ステータスが正しく表示される()
    {
        $user =User::factory()->create();

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow('2026-04-01 10:00:00');

        $user =User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow('2026-04-01 10:00:00');

        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('休憩中');
    }

    public function test_退勤済みの場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow('2026-04-01 10:00:00');

        $user =User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_out' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('退勤済');
    }
}