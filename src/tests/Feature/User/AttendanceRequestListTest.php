<?php

namespace Tests\Feature\User;

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

    private function now(): Carbon
    {
        return Carbon::create(2026, 4, 15, 9, 0);
    }

    public function test_承認待ちにログインユーザーが行った申請がすべて表示されている()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $this->actingAs($user);

        $response = $this->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $this->requestedClockIn(),
            'requested_clock_out' => $this->requestedClockOut(),
            'requested_note' => '電車遅延のため',
        ]);

        $response = $this->get(route('request.list'));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認待ち', '山田', '2026/04/01']);
    }

    public function test_承認済みに管理者が承認した修正申請がすべて表示されている()
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

        $this->actingAs($user);

        $response = $this->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
            'clock_in' => '10:00',
            'clock_out' => '18:00',
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

        $attendanceRequest->update([
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
        ]);

        $response = $this->get(route('request.list', ['status' => 'approved']));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認済み', '山田', '2026/04/01']);
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $this->actingAs($user);

        $response = $this->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $this->requestedClockIn(),
            'requested_clock_out' => $this->requestedClockOut(),
            'requested_note' => '電車遅延のため',
        ]);

        $response = $this->get(route('request.list'));
        $response->assertSee('詳細');
        $response->assertSee(
            'href="' . route('attendance.show', ['id' => $attendance->id]) . '"', false
        );

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}