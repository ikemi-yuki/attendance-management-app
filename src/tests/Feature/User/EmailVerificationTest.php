<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録後認証メールが送信される()
    {
        Notification::fake();

        $response = $this->followingRedirects()->post(route('register'), [
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    public function test_メール認証誘導画面で認証はこちらからボタンを押下するとメール認証サイトに遷移する()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class,
            function ($notification) use ($user, &$verificationUrl) {
                $verificationUrl = $notification->toMail($user)->actionUrl;
                return true;
            }
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance?verified=1');
    }

    public function test_メール認証サイトのメール認証を完了すると勤怠登録画面に遷移する()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this
        ->actingAs($user)
        ->followingRedirects()
        ->get($verificationUrl);

        $response->assertSee('出勤');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}