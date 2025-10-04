<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 会員登録後、認証メールが送信される()
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /** @test */
    public function 「認証はこちらから」ボタンでメール認証サイトに遷移する()
    {
        $user = User::factory()->unverified()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');

        $response = $this->actingAs($user)->get(route('verification.site'));
        $response->assertStatus(200);
    }

    /** @test */
    public function メール認証完了後、勤怠登録画面に遷移する()
    {
        $user = User::factory()->unverified()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('attendance.index'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
