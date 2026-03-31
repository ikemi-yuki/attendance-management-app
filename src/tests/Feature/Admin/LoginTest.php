<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);

        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->followingRedirects()->post(route('admin.login'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);

        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->followingRedirects()->post(route('admin.login'), [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSee('パスワードを入力してください');
    }

    public function test_登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);

        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->followingRedirects()->post(route('admin.login'), [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSee('ログイン情報が登録されていません');
    }
}