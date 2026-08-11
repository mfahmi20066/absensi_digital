<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'sppg_name' => 'SPPG Palopo Wara Timur Benteng',
            'sppg_address' => 'Jl. Pongsimpin, Kel. Benteng, Kec. Wara Timur, Kota Palopo, Sulawesi Selatan',
            'sppg_latitude' => '-2.9921000',
            'sppg_longitude' => '120.1962000',
            'radius_meter' => '100',
            'default_tolerance_minutes' => '15',
        ];

        foreach ($settings as $key => $value) {
            Pengaturan::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
