<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Barcode lama berformat NIP-YYYYMMDD-XXXX (hanya 10.000 kombinasi)
     * mudah ditebak. Semua kode aktif berformat lama diregenerasi menjadi
     * UUID acak agar tidak dapat dipalsukan.
     */
    public function up(): void
    {
        $barcodes = DB::table('barcodes')
            ->where('is_active', true)
            ->get(['id', 'code']);

        foreach ($barcodes as $barcode) {
            if (preg_match('/^[A-Z0-9]+-\d{8}-\d{4}$/', $barcode->code)) {
                DB::table('barcodes')
                    ->where('id', $barcode->id)
                    ->update(['code' => (string) Str::uuid()]);
            }
        }
    }

    public function down(): void
    {
        // Kode lama tidak dapat dipulihkan kembali setelah diregenerasi.
    }
};
