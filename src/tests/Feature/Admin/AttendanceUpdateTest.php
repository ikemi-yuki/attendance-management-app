<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->patch(route('admin.attendance.update', ['id' => $attendance->id]),[
            'clock_in' => '17:00',
            'clock_out' => '09:00',
            'note' => '電車遅延のため',
        ]);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 13:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->patch(route('admin.attendance.update', ['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => [
                $break->id => [
                    'break_start' => '18:00',
                ],
            ],
            'note' => '電車遅延のため',
        ]);

        $response->assertSee('休憩時間が不適切な値です');
    }

    public function test_休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 13:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->patch(route('admin.attendance.update', ['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => [
                $break->id => [
                    'break_start' => '12:00',
                    'break_end' => '18:00',
                ],
            ],
            'note' => '電車遅延のため',
        ]);

        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    public function test_備考欄が未入力の場合エラーメッセージが表示される()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->patch(route('admin.attendance.update', ['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'note' => '',
        ]);

        $response->assertSee('備考を入力してください');
    }
}