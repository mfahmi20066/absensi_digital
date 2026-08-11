<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'label'])]
class Peran extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'roles';

    public function users(): HasMany
    {
        return $this->hasMany(Pengguna::class, 'role_id');
    }
}
