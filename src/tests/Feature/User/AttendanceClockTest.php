<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockTest extends TestCase
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

    private function now(): Carbon
    {
        return $this->clockOutTime();
    }

    public function test_出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow($this->clockInTime());
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('勤務外');
        $response->assertSee('出勤');
        $response->assertSee(route('attendance.clock-in'));

        $response = $this->post(route('attendance.clock-in'));
        $response->assertRedirect(route('clock'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');
    }

    public function test_出勤は一日一回のみできる()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('退勤済');
        $response->assertDontSee(route('attendance.clock-in'));

        $response = $this->post(route('attendance.clock-in'));
        $response->assertStatus(400);
    }

    public function test_退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow($this->clockOutTime());
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
        $response->assertSee('退勤');
        $response->assertSee(route('attendance.clock-out'));

        $response = $this->post(route('attendance.clock-out'));
        $response->assertRedirect(route('clock'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('退勤済');
    }
}