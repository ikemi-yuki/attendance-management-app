<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $otherUser = User::factory()->create(['name' => '田中']);

        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 10:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.attendance.index'));

        $response->assertSee('2026年4月1日の勤怠');
        $response->assertSeeInOrder(['山田', '09:00']);
        $response->assertSeeInOrder(['田中', '10:00']);
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.attendance.index'));

        $response->assertSee('2026年4月1日の勤怠');
        $response->assertSee('2026/04/01');
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        Carbon::setTestNow('2026-04-02 09:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.index'));
        $response->assertSee('前日');

        $response = $this->get(route('admin.attendance.index', ['date' => '2026-04-01']));

        $response->assertSee('2026年4月1日の勤怠');
        $response->assertSeeInOrder(['山田', '09:00']);
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow('2026-04-01 09:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-02',
            'clock_in' => '2026-04-02 09:00:00',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($adminUser, 'admin');

        $response = $this->get(route('admin.attendance.index'));
        $response->assertSee('翌日');

        $response = $this->get(route('admin.attendance.index', ['date' => '2026-04-02']));

        $response->assertSee('2026年4月2日の勤怠');
        $response->assertSeeInOrder(['山田', '09:00']);
    }
}
