<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
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

    private function breakEndTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(13, 0);
    }

    private function now(): Carbon
    {
        return $this->clockOutTime();
    }

    public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSee('山田');
    }

    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSeeInOrder(['2026年', '4月1日']);
    }

    public function test_出勤・退勤にて記されている時間がログインユーザーの打刻と一致している()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSeeInOrder(['出勤・退勤', '09:00', '17:00']);
    }

    public function test_休憩にて記されている時間がログインユーザーの打刻と一致している()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $this->breakStartTime(),
            'break_end' => $this->breakEndTime(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSeeInOrder(['休憩', '12:00', '13:00']);
    }
}