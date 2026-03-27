<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_名前が未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        $response = $this->followingRedirects()->post(route('register'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSee('お名前を入力してください');
    }

    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        $response = $this->followingRedirects()->post(route('register'), [
            'name' => '山田太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_パスワードが8文字未満の場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        $response = $this->followingRedirects()->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    public function test_パスワードが確認用パスワードと一致しない場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        $response = $this->followingRedirects()->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => '123password',
        ]);

        $response->assertSee('パスワードと一致しません');
    }

    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        $response = $this->followingRedirects()->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSee('パスワードを入力してください');
    }

    public function test_フォームに内容が入力されていた場合データが正常に保存される()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);

        $response = $this->followingRedirects()->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => '山田太郎',
        ]);
    }
}
