<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
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

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
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

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
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

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
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

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'note' => '',
        ]);

        $response->assertSee('備考を入力してください');
    }

    public function test_修正申請処理が実行される()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');
        $user = User::factory()->create(['name' => '山田']);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

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

        $response = $this->actingAs($user)->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'breaks' => [
                $break->id => [
                    'break_start' => '12:30',
                    'break_end' => '13:30',
                ],
            ],
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
        ]);

        $attendanceRequest = AttendanceCorrectRequest::where('user_id', $user->id)->first();

        $this->assertDatabaseHas('attendance_correct_request_breaks', [
            'attendance_correct_request_id' => $attendanceRequest->id,
            'attendance_break_id' => $break->id,
            'requested_break_start' => '2026-04-01 12:30:00',
            'requested_break_end' => '2026-04-01 13:30:00',
        ]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('request.list'));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認待ち', '山田', '2026/04/01']);

        $response = $this->get(route('admin.request.show', ['attendance_correct_request_id' => $attendanceRequest->id]));
        $response->assertSee('勤怠詳細');
        $response->assertSee('山田');
        $response->assertSee('<button class="form__button-submit" type="submit">承認</button>', false);
    }
}