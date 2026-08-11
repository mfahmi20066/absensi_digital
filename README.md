# SPPG Palopo Wara Timur Benteng - Sistem Absensi Digital

Website absensi digital berbasis **Laravel 13** untuk Satuan Pelayanan Pemenuhan Gizi (SPPG)
di Kel. Benteng, Kec. Wara Timur, Kota Palopo.

## Fitur Utama

- **3 role**: Admin (kelola penuh), Manajer (monitoring & approval final), Karyawan (absen & pengajuan)
- **Absensi barcode/QR**: tiap karyawan punya QR unik, bisa discan lewat kamera perangkat
- **Absensi kamera**: foto wajah sebagai bukti kehadiran (tanpa face recognition)
- **Pelacakan lokasi wajib**: geolocation dibandingkan radius titik SPPG (flag "luar area")
- **Status otomatis**: Hadir / Telat (sesuai jadwal shift & toleransi) / Izin / Sakit / Cuti / Alpha
- **Pengajuan izin/sakit/cuti** + upload bukti + approval berjenjang
- **Laporan**: harian/bulanan/tahunan, export CSV & cetak
- **Audit trail** seluruh aksi penting
- **Responsive mobile-first** (absensi via HP)

## Persyaratan

- PHP 8.3+, Composer, Node.js (untuk build asset), MySQL
- Ekstensi PHP: `gd` (untuk QR PNG), `fileinfo`

## Instalasi

```bash
composer install
npm install && npm run build
cp .env.example .env        # atau gunakan .env yang sudah ada
# isi kredensial DB di .env (DB_DATABASE=absensi_sppg, dll)
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Akses: `http://127.0.0.1:8000`
|
## Logo

Logo website diambil dari folder `public/images/logos/`:

- `sppg-logo.png` → logo utama
- `sppg-logo-white.png` → versi untuk background gelap
- `favicon.png` → icon browser

Ganti file di folder tersebut dengan logo asli Anda (pertahankan nama file).
Lihat `public/images/logos/BACA_DULU.txt`.

## Pengaturan Lokasi & Radius

Masuk sebagai **Admin → Pengaturan**, isi koordinat (latitude/longitude) titik SPPG
(dari Google Maps: klik kanan titik → salin koordinat) dan radius absensi dalam meter.
Absen di luar radius tetap tercatat tetapi diberi tanda **"Luar Area"**.

## Struktur Database

`roles`, `users`, `employees`, `work_schedules`, `barcodes`, `attendances`,
`leave_requests`, `audit_logs`, `settings` (lihat `PROMPT.md` bagian ERD).

## Cara Absen (Karyawan)

1. Login sebagai karyawan
2. Menu **Absen Masuk / Pulang** → Scan Barcode (pindai QR kartu) **atau** Absen Kamera
3. Browser meminta izin **kamera** & **lokasi** → wajib diizinkan
4. Konfirmasi → sistem mencatat waktu, foto, dan lokasi

QR pada kartu bisa juga dipindai dengan kamera HP biasa → halaman absen terbuka otomatis
dengan data karyawan terisi (`/scan/{kode}`).

## Laporan

**Admin/Manajer → Laporan**: pilih periode (harian/bulanan/tahunan), lalu
Export CSV atau Cetak. Admin juga bisa mengedit/hapus data absensi manual.
