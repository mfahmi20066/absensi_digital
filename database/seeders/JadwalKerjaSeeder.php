<?php

namespace Database\Seeders;

use App\Models\JadwalKerja;
use Illuminate\Database\Seeder;

class JadwalKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            ['name' => 'Shift Pagi', 'time_in' => '07:00', 'time_out' => '15:00', 'tolerance_minutes' => 15],
            ['name' => 'Shift Siang', 'time_in' => '13:00', 'time_out' => '21:00', 'tolerance_minutes' => 15],
        ];

        foreach ($schedules as $schedule) {
            JadwalKerja::firstOrCreate(['name' => $schedule['name']], $schedule);
        }
    }
}
