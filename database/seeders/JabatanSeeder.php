<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            'Kepala SPPG',
            'Pengawas Keuangan & Administrasi',
            'Ahli Gizi',
            'Kepala Dapur',
            'Asisten Kepala Dapur',
            'Juru Masak',
            'Petugas Persiapan & Packing',
            'Petugas Logistik',
            'Petugas Kebersihan & Sanitasi',
            'Pengemudi/Distribusi',
        ];

        foreach ($positions as $name) {
            Jabatan::firstOrCreate(['name' => $name]);
        }
    }
}
