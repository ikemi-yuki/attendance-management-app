<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceNavigationTest extends TestCase
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

    private function now(): Carbon
    {
        return $this->clockOutTime();
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

        $this->actingAs($user);
        $response = $this->get(route('attendance.index'));
        $response->assertSee('前月');

        $response = $this->get(route('attendance.index', ['month' => '2026-03']));
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

        $this->actingAs($user);
        $response = $this->get(route('attendance.index'));
        $response->assertSee('翌月');

        $response = $this->get(route('attendance.index', ['month' => '2026-05']));
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

        $this->actingAs($user);
        $response = $this->get(route('attendance.index'));
        $response->assertSee('詳細');
        $response->assertSee(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));
        $response->assertSee('勤怠詳細');
    }
}