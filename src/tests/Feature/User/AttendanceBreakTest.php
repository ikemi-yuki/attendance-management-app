<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
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

    private function firstBreakStartTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(12, 0);
    }

    private function breakEndTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(12, 30);
    }

    private function secondBreakStartTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(13, 0);
    }

    public function test_休憩入ボタンが正しく機能する()
    {
        Carbon::setTestNow($this->firstBreakStartTime());
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => null,
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');
        $response->assertSee(route('attendance.break-start'));

        $response = $this->post(route('attendance.break-start'));
        $response->assertRedirect(route('clock'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $this->firstBreakStartTime(),
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('休憩中');
    }

    public function test_休憩入は一日に何回でもできる()
    {
        Carbon::setTestNow($this->firstBreakStartTime());
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => null,
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');

        $response = $this->post(route('attendance.break-start'));

        Carbon::setTestNow($this->breakEndTime());
        $response = $this->post(route('attendance.break-end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $this->firstBreakStartTime(),
            'break_end' => $this->breakEndTime(),
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('休憩入');
        $response->assertSee(route('attendance.break-start'));
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow($this->firstBreakStartTime());
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => null,
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');

        $response = $this->post(route('attendance.break-start'));

        $response = $this->get(route('clock'));
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
        $response->assertSee(route('attendance.break-end'));

        Carbon::setTestNow($this->breakEndTime());
        $response = $this->post(route('attendance.break-end'));
        $response->assertRedirect(route('clock'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $this->firstBreakStartTime(),
            'break_end' => $this->breakEndTime(),
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow($this->firstBreakStartTime());
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => null,
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');

        $response = $this->post(route('attendance.break-start'));

        Carbon::setTestNow($this->breakEndTime());
        $response = $this->post(route('attendance.break-end'));

        Carbon::setTestNow($this->secondBreakStartTime());
        $response = $this->post(route('attendance.break-start'));

        $response = $this->get(route('clock'));
        $response->assertSee('休憩戻');
        $response->assertSee(route('attendance.break-end'));

        $this->assertDatabaseCount('attendance_breaks', 2);
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $this->firstBreakStartTime(),
            'break_end' => $this->breakEndTime(),
        ]);
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $this->secondBreakStartTime(),
            'break_end' => null,
        ]);
    }
}