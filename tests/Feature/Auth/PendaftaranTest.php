<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use App\Notifications\KodeOtpVerifikasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test Pengguna',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('verifikasi-otp', absolute: false));

        $user = Pengguna::where('email', 'test@example.com')->first();

        $this->assertAuthenticated();
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->email_otp);
        $this->assertNotNull($user->email_otp_expires_at);

        Notification::assertSentTo($user, KodeOtpVerifikasi::class);
    }
}
