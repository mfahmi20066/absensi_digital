<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use App\Notifications\KodeOtpVerifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResetSandiTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_otp_can_be_sent_for_registered_email(): void
    {
        Notification::fake();

        $user = Pengguna::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertRedirect(route('password.otp'));
        $response->assertSessionHas('reset_email', $user->email);
        $this->assertNotNull($user->fresh()->email_otp);

        Notification::assertSentTo($user, KodeOtpVerifikasi::class);
    }

    public function test_forgot_password_shows_message_when_email_not_registered(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'tidak-ada@example.com']);

        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('email') === 'Email tidak terdaftar.';
        });
    }

    public function test_otp_page_requires_email_from_session(): void
    {
        $response = $this->get('/forgot-password/verifikasi');

        $response->assertRedirect(route('password.request'));
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        $user = Pengguna::factory()->create();
        $kode = $user->generateEmailOtp();

        $response = $this->withSession(['reset_email' => $user->email])->post('/forgot-password/verifikasi/otp', [
            'kode' => $kode,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('reset_otp_terverifikasi', true)
            ->assertRedirect(route('password.otp'));

        $response = $this->withSession(['reset_email' => $user->email, 'reset_otp_terverifikasi' => true])->post('/forgot-password/verifikasi', [
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_password_cannot_be_reset_with_invalid_otp(): void
    {
        $user = Pengguna::factory()->create();
        $user->generateEmailOtp();

        $response = $this->withSession(['reset_email' => $user->email])->post('/forgot-password/verifikasi/otp', [
            'kode' => '000000',
        ]);

        $response->assertSessionHasErrors('kode');
        $this->assertNotNull($user->fresh()->email_otp);
    }

    public function test_password_cannot_be_reset_without_otp_verification(): void
    {
        $user = Pengguna::factory()->create();
        $user->generateEmailOtp();

        $response = $this->withSession(['reset_email' => $user->email])->post('/forgot-password/verifikasi', [
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $response->assertRedirect(route('password.otp'));
        $this->assertFalse(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_reset_password_shows_message_when_email_not_found(): void
    {
        $response = $this->withSession(['reset_email' => 'tidak-ada@example.com'])->post('/forgot-password/verifikasi/otp', [
            'kode' => '123456',
        ]);

        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('kode') === 'Email tidak ditemukan.';
        });
    }
}
