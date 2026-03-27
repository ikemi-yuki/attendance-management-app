<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSee('山田');
    }

    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSee('2026年');
        $response->assertSee('4月1日');
    }

    public function test_出勤・退勤にて記されている時間がログインユーザーの打刻と一致している()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSeeInOrder(['出勤・退勤', '09:00', '17:00']);
    }

    public function test_休憩にて記されている時間がログインユーザーの打刻と一致している()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertSeeInOrder(['休憩', '12:00', '13:00']);
    }
}
