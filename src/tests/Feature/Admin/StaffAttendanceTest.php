<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.attendance.monthly', ['id' => $user->id]));

        $response->assertSee('山田さんの勤怠');
        $response->assertSee($attendance->clock_in->format('H:i'));
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

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.monthly', ['id' => $user->id]));
        $response->assertSee('前月');

        $response = $this->get(route('admin.attendance.monthly', [
            'id' => $user->id,
            'month' => '2026-03',
        ]));

        $response->assertSee($attendance->clock_in->format('H:i'));
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

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.monthly', ['id' => $user->id]));
        $response->assertSee('翌月');

        $response = $this->get(route('admin.attendance.monthly', [
            'id' => $user->id,
            'month' => '2026-05',
        ]));

        $response->assertSee($attendance->clock_in->format('H:i'));
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
        $response->assertSee(
            'href="' . route('admin.attendance.show', ['id' => $attendance->id]) . '"', false
        );

        $response = $this->get(route('admin.attendance.show', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
