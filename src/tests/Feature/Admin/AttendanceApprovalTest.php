<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $attendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => now(),
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);

        $otherUser = User::factory()->create(['name' => '田中']);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-04-02',
            'clock_in' => '2026-04-02 09:30:00',
            'clock_out' => '2026-04-02 17:30:00',
        ]);

        $otherAttendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $otherUser->id,
            'attendance_id' => $otherAttendance->id,
            'requested_clock_in' => '2026-04-02 10:30:00',
            'requested_clock_out' => '2026-04-02 18:30:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => now(),
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('request.list', ['status' => 'pending']));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認待ち', '山田',]);
        $response->assertSeeInOrder(['承認待ち', '田中',]);
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $attendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => now(),
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
        ]);

        $otherUser = User::factory()->create(['name' => '田中']);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-04-02',
            'clock_in' => '2026-04-02 09:30:00',
            'clock_out' => '2026-04-02 17:30:00',
        ]);

        $otherAttendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $otherUser->id,
            'attendance_id' => $otherAttendance->id,
            'requested_clock_in' => '2026-04-02 10:30:00',
            'requested_clock_out' => '2026-04-02 18:30:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => now(),
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('request.list', ['status' => 'approved']));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認済み', '山田']);
        $response->assertSeeInOrder(['承認済み', '田中']);
    }

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

        $attendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => now(),
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.request.show', ['attendance_correct_request_id' => $attendanceRequest->id]));
        $response->assertSee('勤怠詳細');
        $response->assertSee('山田');
        $response->assertSeeInOrder(['2026年', '4月1日']);
        $response->assertSeeInOrder(['出勤・退勤', '10:00', '18:00']);
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

        $attendanceRequest = AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
            'requested_at' => now(),
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.request.show', ['attendance_correct_request_id' => $attendanceRequest->id]));
        $response->assertSee('<button class="form__button-submit" type="submit">承認</button>', false);

        $response = $this->patch(route('admin.request.approve', ['attendance_correct_request_id' => $attendanceRequest->id]));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 10:00:00',
            'clock_out' => '2026-04-01 18:00:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
            'approved_by' => $adminUser->id,
        ]);
    }
}