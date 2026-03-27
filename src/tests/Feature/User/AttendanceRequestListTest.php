<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceRequestListTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちにログインユーザーが行った申請がすべて表示されている()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
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
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
        ]);

        $response = $this->get(route('request.list'));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認待ち', '山田', '2026/04/01']);
    }

    public function test_承認済みに管理者が承認した修正申請がすべて表示されている()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
        ]);

        $response = $this->actingAs($user)->followingRedirects()->post(route('attendance.store', ['id' => $attendance->id]),[
            'clock_in' => '10:00',
            'clock_out' => '18:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
            'requested_note' => '電車遅延のため',
        ]);

        $attendanceRequest = AttendanceCorrectRequest::first();

        $response = $this->actingAs($adminUser, 'admin')->patch(route('admin.request.approve', ['attendance_correct_request_id' => $attendanceRequest->id]));

        $response = $this->actingAs($user)->get(route('request.list', ['status' => 'approved']));
        $response->assertSee('申請一覧');
        $response->assertSeeInOrder(['承認済み', '山田', '2026/04/01']);
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow('2026-04-15 09:00:00');

        $user = User::factory()->create(['name' => '山田']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-04-01',
            'clock_in' => '2026-04-01 09:00:00',
            'clock_out' => '2026-04-01 17:00:00',
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
            'requested_clock_in' => '2026-04-01 10:00:00',
            'requested_clock_out' => '2026-04-01 18:00:00',
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