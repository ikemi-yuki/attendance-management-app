<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
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

    private function otherUserClockInTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(10, 0);
    }

    private function now(): Carbon
    {
        return $this->clockOutTime();
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow($this->clockInTime());
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('勤務外');

        $response = $this->post(route('attendance.clockIn'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01(水)', '09:00']);
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow($this->breakStartTime());
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');

        $response = $this->post(route('attendance.breakStart'));

        Carbon::setTestNow($this->breakEndTime());
        $response = $this->post(route('attendance.breakEnd'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $this->breakStartTime(),
            'break_end' => $this->breakEndTime(),
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01(水)', '01:00']);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow($this->clockInTime());
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('勤務外');

        $response = $this->post(route('attendance.clockIn'));

        Carbon::setTestNow($this->clockOutTime());
        $response = $this->post(route('attendance.clockOut'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01(水)', '17:00']);
    }

    public function test_自分が行った勤怠情報がすべて表示される()
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

        $otherUser = User::factory()->create();
        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->otherUserClockInTime(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01(水)', '09:00', '17:00', '01:00', '07:00']);
        $response->assertDontSee('10:00');
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('2026/04');
    }
}