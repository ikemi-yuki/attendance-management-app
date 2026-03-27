<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceClockTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow('2026-04-01 09:00:00');
        $user =User::factory()->create();

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        $response = $this->post(route('attendance.clock-in'));
        $response->assertRedirect(route('clock'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');
    }

    public function test_出勤は一日一回のみできる()
    {
        $user =User::factory()->create();

        Attendance::factory()->today()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('退勤済');
        $response->assertDontSee('value="出勤"');

        $response = $this->post(route('attendance.clock-in'));
        $response->assertStatus(400);
    }

    public function test_退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow('2026-04-01 17:00:00');
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
        $response->assertSee('退勤');

        $response = $this->post(route('attendance.clock-out'));
        $response->assertRedirect(route('clock'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $response = $this->get(route('clock'));
        $response->assertSee('退勤済');
    }
}
