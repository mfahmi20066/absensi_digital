<?php

namespace Database\Seeders;

use App\Models\Peran;
use Illuminate\Database\Seeder;

class PeranSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'label' => 'Administrator'],
            ['name' => 'manajer', 'label' => 'Manajer'],
            ['name' => 'karyawan', 'label' => 'Karyawan'],
        ];

        foreach ($roles as $role) {
            Peran::firstOrCreate(['name' => $role['name']], ['label' => $role['label']]);
        }
    }
}
