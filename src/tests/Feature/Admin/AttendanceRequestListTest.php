<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRequestListTest extends TestCase
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

    private function requestedClockIn(): Carbon
    {
        return $this->baseDate()->copy()->setTime(10, 0);
    }

    private function requestedClockOut(): Carbon
    {
        return $this->baseDate()->copy()->setTime(18, 0);
    }

    private function otherUserClockInTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(9, 30);
    }

    private function otherUserClockOutTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(17, 30);
    }

    private function otherUserRequestedClockIn(): Carbon
    {
        return $this->baseDate()->copy()->setTime(10, 30);
    }

    private function otherUserRequestedClockOut(): Carbon
    {
        return $this->baseDate()->copy()->setTime(18, 30);
    }

    private function now(): Carbon
    {
        return Carbon::create(2026, 4, 15, 9, 0);
    }

    public function test_承認待ちの修正申請が全て表示されている()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $this->requestedClockIn(),
            'requested_clock_out' => $this->requestedClockOut(),
            'requested_note' => '電車遅延のため',
            'requested_at' => $this->now(),
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);

        $otherUser = User::factory()->create(['name' => '田中']);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->otherUserClockInTime(),
            'clock_out' => $this->otherUserClockOutTime(),
        ]);

        AttendanceCorrectRequest::create([
            'user_id' => $otherUser->id,
            'attendance_id' => $otherAttendance->id,
            'requested_clock_in' => $this->otherUserRequestedClockIn(),
            'requested_clock_out' => $this->otherUserRequestedClockOut(),
            'requested_note' => '電車遅延のため',
            'requested_at' => $this->now(),
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
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $this->requestedClockIn(),
            'requested_clock_out' => $this->requestedClockOut(),
            'requested_note' => '電車遅延のため',
            'requested_at' => $this->now(),
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
        ]);

        $otherUser = User::factory()->create(['name' => '田中']);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->otherUserClockInTime(),
            'clock_out' => $this->otherUserClockOutTime(),
        ]);

        AttendanceCorrectRequest::create([
            'user_id' => $otherUser->id,
            'attendance_id' => $otherAttendance->id,
            'requested_clock_in' => $this->otherUserRequestedClockIn(),
            'requested_clock_out' => $this->otherUserRequestedClockOut(),
            'requested_note' => '電車遅延のため',
            'requested_at' => $this->now(),
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('request.list', ['status' => 'approved']));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認済み', '山田']);
        $response->assertSeeInOrder(['承認済み', '田中']);
    }
}