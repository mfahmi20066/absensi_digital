<?php

namespace Tests\Feature\Auth;

use App\Models\Pengguna;
use App\Notifications\KodeOtpVerifikasi;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerifikasiOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_otp_dialihkan_untuk_user_yang_sudah_terverifikasi(): void
    {
        $user = Pengguna::factory()->create();

        $response = $this->actingAs($user)->get('/verifikasi-otp');

        $response->assertRedirect(route('dasbor', absolute: false));
    }

    public function test_halaman_otp_dapat_dirender_untuk_user_belum_terverifikasi(): void
    {
        $user = Pengguna::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verifikasi-otp');

        $response->assertStatus(200);
    }

    public function test_kode_otp_yang_benar_menverifikasi_email(): void
    {
        Event::fake();
        $user = Pengguna::factory()->unverified()->create();
        $kode = $user->generateEmailOtp();

        $response = $this->actingAs($user)->post('/verifikasi-otp', ['kode' => $kode]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dasbor', absolute: false));
    }

    public function test_kode_otp_yang_salah_ditolak(): void
    {
        $user = Pengguna::factory()->unverified()->create();
        $user->generateEmailOtp();

        $response = $this->actingAs($user)->post('/verifikasi-otp', ['kode' => '000000']);

        $response->assertSessionHasErrors('kode');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_kode_otp_yang_kadaluwarsa_ditolak(): void
    {
        $user = Pengguna::factory()->unverified()->create();
        $user->update(['email_otp_expires_at' => now()->subMinute()]);
        $kode = $user->generateEmailOtp();
        $user->update(['email_otp_expires_at' => now()->subMinute()]);

        $response = $this->actingAs($user)->post('/verifikasi-otp', ['kode' => $kode]);

        $response->assertSessionHasErrors('kode');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_kirim_ulang_mengirim_kode_otp_baru(): void
    {
        Notification::fake();
        $user = Pengguna::factory()->unverified()->create();

        $response = $this->actingAs($user)->post('/verifikasi-otp/kirim-ulang');

        $response->assertSessionHas('status');
        $this->assertNotNull($user->fresh()->email_otp);
        Notification::assertSentTo($user, KodeOtpVerifikasi::class);
    }
}
