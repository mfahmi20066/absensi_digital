<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CekDasborTest extends TestCase
{
    use RefreshDatabase;

    public function test_dasbor_renders_for_verified_user(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this->actingAs($user)->get('/dasbor');

        $response->assertStatus(200);
    }
}
