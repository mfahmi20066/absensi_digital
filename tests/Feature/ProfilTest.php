<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profil');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profil', [
                'name' => 'Test Pengguna',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profil');

        $user->refresh();

        $this->assertSame('Test Pengguna', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profil', [
                'name' => 'Test Pengguna',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profil');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profil', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profil')
            ->delete('/profil', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profil');

        $this->assertNotNull($user->fresh());
    }
}
