<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function baseDate(): Carbon
    {
        return Carbon::create(2026, 4, 1);
    }

    private function previousMonthDate(): Carbon
    {
        return $this->baseDate()->copy()->subMonth()->setDay(15);
    }

    private function nextMonthDate(): Carbon
    {
        return $this->baseDate()->copy()->addMonth()->setDay(15);
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

    private function now(): Carbon
    {
        return $this->clockOutTime();
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
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

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->otherUserClockInTime(),
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.attendance.monthly', ['id' => $user->id]));

        $response->assertSee('山田さんの勤怠');
        $response->assertSeeInOrder(['04/01(水)', '09:00', '17:00', '01:00', '07:00']);
        $response->assertDontSee('田中');
        $response->assertDontSee('08:00');
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->previousMonthDate(),
            'clock_in' => $this->previousMonthDate()->copy()->setTime(8, 0),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.monthly', ['id' => $user->id]));
        $response->assertSee('前月');
        $response->assertSee(
            route('admin.attendance.monthly', [
                'id' => $user->id,
                'month' => '2026-03'
            ])
        );

        $response = $this->get(route('admin.attendance.monthly', [
            'id' => $user->id,
            'month' => '2026-03',
        ]));

        $response->assertSeeInOrder(['03/15(日)', '08:00']);
        $response->assertDontSee('09:00');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->nextMonthDate(),
            'clock_in' => $this->nextMonthDate()->copy()->setTime(8, 0),
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.monthly', ['id' => $user->id]));
        $response->assertSee('翌月');
        $response->assertSee(
            route('admin.attendance.monthly', [
                'id' => $user->id,
                'month' => '2026-05'
            ])
        );

        $response = $this->get(route('admin.attendance.monthly', [
            'id' => $user->id,
            'month' => '2026-05',
        ]));

        $response->assertSeeInOrder(['05/15(金)', '08:00']);
        $response->assertDontSee('09:00');
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.monthly', ['id' => $user->id]));
        $response->assertSee('詳細');
        $response->assertSee(route('admin.attendance.show', ['id' => $attendance->id]));

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}