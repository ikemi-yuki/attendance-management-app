<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow('2026-04-01 09:00:00');
        $user =User::factory()->create();

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('勤務外');

        $response = $this->post(route('attendance.clock-in'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01(水)', '09:00']);
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        $user =User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => null,
        ]);

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('出勤中');

        $response = $this->post(route('attendance.break-start'));

        Carbon::setTestNow('2026-04-01 12:30:00');
        $response = $this->post(route('attendance.break-end'));

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 12:30:00',
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01', '00:30']);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow('2026-04-01 09:00:00');
        $user =User::factory()->create();

        $this ->actingAs($user);

        $response = $this->get(route('clock'));
        $response->assertSee('勤務外');

        $response = $this->post(route('attendance.clock-in'));

        Carbon::setTestNow('2026-04-01 17:00:00');
        $response = $this->post(route('attendance.clock-out'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $response = $this->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01(水)', '17:00']);
    }

    public function test_自分が行った勤怠情報がすべて表示される()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-04-01 12:00:00',
            'break_end' => '2026-04-01 13:00:00',
        ]);

        $otherUser = User::factory()->create();
        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 10:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSeeInOrder(['04/01(水)', '09:00', '17:00', '01:00', '07:00']);
        $response->assertDontSee('10:00');
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow('2026-04-01 09:00:00');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('2026/04');
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow('2026-04-10 09:00:00');
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-15',
            'clock_in' => '2026-03-15 09:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 08:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('前月');

        $response = $this->get(route('attendance.index', ['month' => '2026-03']));
        $response->assertSeeInOrder(['03/15(日)', '09:00']);
        $response->assertDontSee('08:00');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow('2026-04-10 09:00:00');
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-05-15',
            'clock_in' => '2026-05-15 09:00:00',
        ]);

        $currentAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 08:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('翌月');

        $response = $this->get(route('attendance.index', ['month' => '2026-05']));
        $response->assertSeeInOrder(['05/15(金)', '09:00']);
        $response->assertDontSee('08:00');
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('詳細');
        $response->assertSee(route('attendance.show', ['id' => $attendance->id]));

        $response = $this->get(route('attendance.show', ['id' => $attendance->id]));
        $response->assertSee('勤怠詳細');
    }
}