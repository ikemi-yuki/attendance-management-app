<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function baseDate(): Carbon
    {
        return Carbon::create(2026, 4, 1);
    }

    private function previousDate(): Carbon
    {
        return $this->baseDate()->copy()->subDay();
    }

    private function nextDate(): Carbon
    {
        return $this->baseDate()->copy()->addDay();
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

    private function otherUserClockInTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(10, 0);
    }

    private function otherUserClockOutTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(18, 0);
    }

    private function otherUserBreakStartTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(12, 30);
    }

    private function otherUserBreakEndTime(): Carbon
    {
        return $this->baseDate()->copy()->setTime(13, 30);
    }

    private function now(): Carbon
    {
        return $this->baseDate()->copy()->setTime(18, 0);
    }

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $this->breakStartTime(),
            'break_end' => $this->breakEndTime(),
        ]);

        $otherUser = User::factory()->create(['name' => '田中']);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->otherUserClockInTime(),
            'clock_out' => $this->otherUserClockOutTime(),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $otherAttendance->id,
            'break_start' => $this->otherUserBreakStartTime(),
            'break_end' => $this->otherUserBreakEndTime(),
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.attendance.index'));

        $response->assertSee('2026年4月1日の勤怠');
        $response->assertSeeInOrder(['山田', '09:00', '17:00', '01:00', '07:00',]);
        $response->assertSeeInOrder(['田中', '10:00', '18:00', '01:00', '07:00',]);
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow($this->now());
        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.attendance.index'));

        $response->assertSee('2026年4月1日の勤怠');
        $response->assertSee('2026/04/01');
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->previousDate(),
            'clock_in' => $this->previousDate()->copy()->setTime(9, 0),
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.index'));
        $response->assertSee('前日');
        $response->assertSee(route('admin.attendance.index', ['date' => '2026-03-31']));

        $response = $this->get(route('admin.attendance.index', ['date' => '2026-03-31']));

        $response->assertSee('2026年3月31日の勤怠');
        $response->assertSeeInOrder(['山田', '09:00']);
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create(['name' => '山田']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->nextDate(),
            'clock_in' => $this->nextDate()->copy()->setTime(9, 0),
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.index'));
        $response->assertSee('翌日');
        $response->assertSee(route('admin.attendance.index', ['date' => '2026-04-02']));

        $response = $this->get(route('admin.attendance.index', ['date' => '2026-04-02']));

        $response->assertSee('2026年4月2日の勤怠');
        $response->assertSeeInOrder(['山田', '09:00']);
    }
}