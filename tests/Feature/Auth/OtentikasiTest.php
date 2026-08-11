<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtentikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dasbor', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = Pengguna::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_shows_message_when_email_not_found(): void
    {
        $response = $this->post('/login', [
            'email' => 'tidak-ada@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('email') === 'Email tidak ditemukan.';
        });
    }

    public function test_login_shows_message_when_password_is_wrong(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('password') === 'Kata sandi salah.';
        });
    }

    public function test_users_can_logout(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
