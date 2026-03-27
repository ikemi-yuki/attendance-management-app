<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StaffListTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの氏名・メールアドレスを確認できる()
    {
        Carbon::setTestNow('2026-04-01 12:00:00');

        $user = User::factory()->create([
            'name' => '山田',
            'email' => 'yamada@example.com',
        ]);

        $otherUser = User::factory()->create([
            'name' => '田中',
            'email' => 'tanaka@example.com',
        ]);

        $adminUser = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.staff.index'));

        $response->assertSee('スタッフ一覧');
        $response->assertSeeInOrder(['山田', 'yamada@example.com']);
        $response->assertSeeInOrder(['田中', 'tanaka@example.com']);
    }
}
