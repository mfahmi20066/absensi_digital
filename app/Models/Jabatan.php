<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Jabatan extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'positions';

    public function karyawan(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'position_id');
    }
}
