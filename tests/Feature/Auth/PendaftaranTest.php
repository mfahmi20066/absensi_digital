<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response = $this->post('/register', [
            'name' => 'Test Pengguna',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('login', absolute: false));

        $user = Pengguna::where('email', 'test@example.com')->first();

        $this->assertGuest();
        $this->assertSame('pending', $user->status);
        $this->assertNull($user->email_otp);
        $this->assertNull($user->email_verified_at);
    }

    public function test_pending_user_cannot_login_before_admin_activation(): void
    {
        $role = Peran::firstOrCreate(['name' => 'karyawan'], ['label' => 'Karyawan']);

        Pengguna::create([
            'name' => 'Budi Pending',
            'email' => 'budi@example.com',
            'password' => 'password',
            'role_id' => $role->id,
            'status' => 'pending',
        ]);

        $response = $this->post('/login', [
            'email' => 'budi@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
