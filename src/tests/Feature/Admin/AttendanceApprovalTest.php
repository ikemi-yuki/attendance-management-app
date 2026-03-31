<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use App\Models\AttendanceCorrectRequestBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');
        $user = User::factory()->create(['name' => '山田']);

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

        $attendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => Carbon::now(),
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);

        AttendanceCorrectRequestBreak::create([
            'attendance_correct_request_id' => $attendanceRequest->id,
            'attendance_break_id' => $break->id,
            'requested_break_start' => '2026-04-01 12:30:00',
            'requested_break_end' => '2026-04-01 13:30:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.request.show', ['attendance_correct_request_id' => $attendanceRequest->id]));
        $response->assertSee('勤怠詳細');
        $response->assertSee('山田');
        $response->assertSeeInOrder(['2026年', '4月1日']);
        $response->assertSeeInOrder(['出勤・退勤', '10:00', '18:00']);
        $response->assertDontSee('09:00');
        $response->assertDontSee('17:00');
        $response->assertSeeInOrder(['休憩', '12:30', '13:30']);
        $response->assertDontSee('12:00');
        $response->assertDontSee('13:00');
        $response->assertSee('電車遅延のため');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');
        $user = User::factory()->create(['name' => '山田']);

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

        $attendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => Carbon::now(),
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);

        AttendanceCorrectRequestBreak::create([
            'attendance_correct_request_id' => $attendanceRequest->id,
            'attendance_break_id' => $break->id,
            'requested_break_start' => '2026-04-01 12:30:00',
            'requested_break_end' => '2026-04-01 13:30:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.request.show', ['attendance_correct_request_id' => $attendanceRequest->id]));
        $response->assertSee('<button class="form__button-submit" type="submit">承認</button>', false);

        $response = $this->followingRedirects()->patch(route('admin.request.approve', ['attendance_correct_request_id' => $attendanceRequest->id]));

        $response->assertSee('承認済み');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 10:00:00',
            'clock_out' => '2026-04-01 18:00:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'id' => $break->id,
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:30:00',
            'break_end' => '2026-04-01 13:30:00',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $attendanceRequest->id,
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
            'approved_by' => $adminUser->id,
        ]);
    }
}