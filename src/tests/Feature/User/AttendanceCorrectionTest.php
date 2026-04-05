<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
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

    private function now(): Carbon
    {
        return Carbon::create(2026, 4, 15, 9, 0);
    }

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.apply', ['id' => $attendance->id]),[
            'clock_in' => '17:00',
            'clock_out' => '09:00',
            'note' => '電車遅延のため',
        ]);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

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

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.apply', ['id' => $attendance->id]),[
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
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

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

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.apply', ['id' => $attendance->id]),[
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
        Carbon::setTestNow($this->now());
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $this->baseDate(),
            'clock_in' => $this->clockInTime(),
            'clock_out' => $this->clockOutTime(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->followingRedirects()->post(route('attendance.apply', ['id' => $attendance->id]),[
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'note' => '',
        ]);

        $response->assertSee('備考を記入してください');
    }
}