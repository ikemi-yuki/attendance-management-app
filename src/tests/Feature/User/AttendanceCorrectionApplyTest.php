<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionApplyTest extends TestCase
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

    private function breakStartTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(12, 0);
    }

    private function breakEndTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(13, 0);
    }

    private function requestedClockIn(): Carbon
    {
        return $this->baseDate()->copy()->setTime(10, 0);
    }

    private function requestedClockOut(): Carbon
    {
        return $this->baseDate()->copy()->setTime(18, 0);
    }

    private function requestedBreakStart(): Carbon
    {
        return $this->baseDate()->copy()->setTime(12, 30);
    }

    private function requestedBreakEnd(): Carbon
    {
        return $this->baseDate()->copy()->setTime(13, 30);
    }

    private function now(): Carbon
    {
        return Carbon::create(2026, 4, 15, 9, 0);
    }

    public function test_修正申請処理が実行される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $break = AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $this->breakStartTime(),
            'break_end' => $this->breakEndTime(),
        ]);

        $response = $this->actingAs($user)->followingRedirects()->post(route('attendance.apply', ['id' => $attendance->id]),[
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
            'requested_clock_in' => $this->requestedClockIn(),
            'requested_clock_out' => $this->requestedClockOut(),
            'requested_note' => '電車遅延のため',
        ]);

        $attendanceRequest = AttendanceCorrectRequest::where('user_id', $user->id)->first();

        $this->assertDatabaseHas('attendance_correct_request_breaks', [
            'attendance_correct_request_id' => $attendanceRequest->id,
            'attendance_break_id' => $break->id,
            'requested_break_start' => $this->requestedBreakStart(),
            'requested_break_end' => $this->requestedBreakEnd(),
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