<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

#[Fillable(['name', 'email', 'password', 'role_id', 'photo_profile', 'status', 'email_verified_at', 'email_otp', 'email_otp_expires_at'])]
#[Hidden(['password', 'remember_token'])]
class Pengguna extends Authenticatable
{
    protected $table = 'users';

    /** @use HasFactory<PenggunaFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function generateEmailOtp(): string
    {
        $kode = (string) random_int(100000, 999999);

        $this->update([
            'email_otp' => Hash::make($kode),
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        return $kode;
    }

    public function verifyEmailOtp(string $kode): bool
    {
        if (! $this->email_otp || ! $this->email_otp_expires_at || $this->email_otp_expires_at->isPast()) {
            return false;
        }

        if (! Hash::check($kode, $this->email_otp)) {
            return false;
        }

        $this->update([
            'email_verified_at' => now(),
            'email_otp' => null,
            'email_otp_expires_at' => null,
        ]);

        return true;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Peran::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Karyawan::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isManajer(): bool
    {
        return $this->role?->name === 'manajer';
    }

    public function isKaryawan(): bool
    {
        return $this->role?->name === 'karyawan';
    }
}
