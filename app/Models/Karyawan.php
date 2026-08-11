<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'work_schedule_id', 'nip', 'position', 'position_id', 'phone', 'join_date', 'status', 'leave_balance', 'leave_balance_year'])]
class Karyawan extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'employees';

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class, 'work_schedule_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'position_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class, 'employee_id');
    }

    public function activeBarcode(): HasOne
    {
        return $this->hasOne(Barcode::class, 'employee_id')
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString()))
            ->latestOfMany();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Absensi::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'employee_id');
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(Lembur::class, 'employee_id');
    }

    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(KoreksiAbsensi::class, 'employee_id');
    }

    public function ensureLeaveQuota(): void
    {
        $quota = (int) Pengaturan::get('leave_quota', '12');
        $year = (int) now()->year;

        if ($this->leave_balance_year !== $year || $this->leave_balance === null) {
            $this->update([
                'leave_balance' => $quota,
                'leave_balance_year' => $year,
            ]);
        }
    }

    public function getSisaCutiAttribute(): int
    {
        $this->ensureLeaveQuota();

        return (int) $this->leave_balance;
    }
}
