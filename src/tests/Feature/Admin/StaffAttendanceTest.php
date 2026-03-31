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

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        Carbon::setTestNow('2026-04-01 20:00:00');
        $user = User::factory()->create(['name' => '山田']);

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

        $otherUser = User::factory()->create(['name' => '田中']);

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 08:00:00',
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
        Carbon::setTestNow('2026-04-10 09:00:00');
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-03-15',
            'clock_in' => '2026-03-15 09:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-02-15',
            'clock_in' => '2026-02-15 08:00:00',
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

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-15',
            'clock_in' => '2026-04-15 08:00:00',
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