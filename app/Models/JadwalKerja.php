<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'time_in', 'time_out', 'tolerance_minutes'])]
class JadwalKerja extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'work_schedules';

    protected function casts(): array
    {
        return [
            'time_in' => 'datetime:H:i',
            'time_out' => 'datetime:H:i',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'work_schedule_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Absensi::class, 'work_schedule_id');
    }
}
