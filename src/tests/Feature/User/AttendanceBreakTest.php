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

    public function test_休憩入ボタンが正しく機能する()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
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
            'break_start' => '2026-04-01 12:00:00',
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('休憩中');
    }

    public function test_休憩入は一日に何回でもできる()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');

        $response = $this->post(route('attendance.break-start'));

        Carbon::setTestNow('2026-04-01 12:30:00');
        $response = $this->post(route('attendance.break-end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 12:30:00',
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('休憩入');
        $response->assertSee(route('attendance.break-start'));
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
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

        Carbon::setTestNow('2026-04-01 12:30:00');
        $response = $this->post(route('attendance.break-end'));
        $response->assertRedirect(route('clock'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 12:30:00',
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');

        $response = $this->post(route('attendance.break-start'));

        Carbon::setTestNow('2026-04-01 12:30:00');
        $response = $this->post(route('attendance.break-end'));

        Carbon::setTestNow('2026-04-01 13:00:00');
        $response = $this->post(route('attendance.break-start'));

        $response = $this->get(route('clock'));
        $response->assertSee('休憩戻');
        $response->assertSee(route('attendance.break-end'));

        $this->assertDatabaseCount('attendance_breaks', 2);
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 12:30:00',
        ]);
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 13:00:00',
            'break_end' => null,
        ]);
    }
}