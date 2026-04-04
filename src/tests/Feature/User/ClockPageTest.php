<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockPageTest extends TestCase
{
    use RefreshDatabase;

    private function baseDate(): Carbon
    {
        return Carbon::create(2026, 4, 1);
    }

    private function clockInTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(9, 0);
    }

    private function clockOutTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(17, 0);
    }

    private function breakStartTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(12, 0);
    }

    private function now(): Carbon
    {
        return $this->clockOutTime();
    }

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow($this->now());
        $user =User::factory()->create();

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('2026年4月1日(水)');
        $response->assertSee('17:00');
    }

    public function test_勤務外の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow($this->now());
        $user =User::factory()->create();

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow($this->now());
        $user =User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow($this->now());
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $this->breakStartTime(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('休憩中');
    }

    public function test_退勤済みの場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow($this->now());
        $user =User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $response = $this->actingAs($user)->get(route('clock'));

        $response->assertSee('退勤済');
    }
}