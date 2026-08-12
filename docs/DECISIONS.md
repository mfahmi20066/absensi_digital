# Keputusan Desain — SPPG Palopo

Dokumen ini mencatat keputusan desain yang **tidak obvious** hanya dari membaca
kode sepintas: apa yang dipilih, kenapa (inferensi dari kode/komentar/konteks),
dan opsi yang tidak dipilih. Sebagian besar terkait hasil audit keamanan yang
telah dieksekusi — lihat `laporan.md` di root untuk detail remediasinya.

## A. Autentikasi & Keamanan

### A1. Verifikasi email via OTP, bukan link
- **APA**: Login memakai email+password, lalu pengguna harus memverifikasi email
  lewat **kode OTP 6 digit** (route `verifikasi-otp`), bukan tautan
  bertanda tangan khas Breeze.
- **KENAPA**: Lebih praktis di lingkungan non-teknis (SPPG/HP) daripada klik link;
  OTP di-hash di DB (`Pengguna::generateEmailOtp`), kedaluwarsa 10 menit, dan
  ada rate limit per user (1x/60 dtk kirim ulang) + `throttle:10,1` per IP.
- **TIDAK DIPILIH**: link `signed` default Breeze (tetap ada route `verify-email/{id}/{hash}`
  tapi alur utama pakai OTP).

### A2. Role-based access memakai middleware kustom `peran:`, cek role BY NAME
- **APA**: alias middleware `peran` → `PastikanPeran`; helper `isAdmin()/isManajer()/isKaryawan()`
  membandingkan `role->name`.
- **KENAPA**: tanpa dependency package role; sederhana & cukup untuk 3 role statis.
- **TIDAK DIPILIH**: spatie/laravel-permission (role & permission terpisah,
  lebih berat untuk kasus ini).
- **Catatan penting**: karena cek berdasar nama, jangan mengubah isi kolom
  `roles.name` tanpa menyesuaikan middleware & helper.

### A3. Registrasi publik → akun `pending` (tanpa auto-login & tanpa OTP)
- **APA**: user yang mendaftar via `/register` dibuat `status='pending'`, tidak
  langsung login, tidak dikirimi OTP; admin harus mengaktifkan via `/admin/pengguna`.
- **KENAPA**: hasil audit P0-4 (registrasi terbuka disalahgunakan).
- **TIDAK DIPILIH**: registrasi tertutup penuh, atau verifikasi email otomatis
  tanpa persetujuan admin.

### A4. Rate limit berlapis
- **APA**: `throttle:10,1` pada `POST /login`, `POST /absen/barcode`,
  `verifikasi-otp/kirim-ulang`; `throttle:6,1` pada OTP verify, forgot-password,
  verifikasi email; plus limiter per email+IP (5 percobaan) di `PermintaanMasuk`.
- **KENAPA**: mitigasi brute-force login & tebakan barcode (audit P0-1, P0-2, P2-11).
- **TIDAK DIPILIH**: captcha / 2FA penuh.

### A5. Kode barcode di-regenerasi menjadi UUID
- **APA**: kode barcode sekarang `Str::uuid()` (36 karakter acak) — sebelumnya
  `NIP-YYYYMMDD-XXXX` (~10.000 kombinasi, bisa ditebak).
- **KENAPA**: audit P0-2; ditambah constraint route `/scan/{code}` `[A-Za-z0-9-]{8,}`
  dan throttle endpoint publik.
- **TIDAK DIPILIH**: mengembalikan format lama yang ramah dibaca manusia.

### A6. Foto absen divalidasi isi + re-encode GD
- **APA**: `storeBase64Photo()` memeriksa MIME sungguhan (`finfo`) lalu
  **re-encode ulang** dengan GD (`imagecreatefromstring` → `imagejpeg` 85).
- **KENAPA**: mencegah file polyglot/SVG-stored-XSS di upload foto (audit P0-3).
- **Trade-off**: hasil foto selalu JPEG (kualitas 85) walau aslinya PNG; galat
  kini berupa `ValidationException` (redirect + pesan), bukan error page.

### A7. Security headers global + CSP dengan `unsafe-inline`/`unsafe-eval`
- **APA**: middleware `HeaderKeamanan` (X-Frame-Options, nosniff,
  Referrer-Policy, Permissions-Policy, HSTS saat HTTPS) + CSP dasar.
- **KENAPA**: audit P2-7. CSP **tidak bisa** memakai `nonce` karena kode memakai
  script inline tema (`localStorage` theme) dan Alpine yang memerlukan
  `unsafe-inline`/`unsafe-eval`. Nilai yang didapat di sini adalah pembatasan
  sumber (source restriction), bukan anti-XSS penuh — ini trade-off eksplisit.
- **Catatan**: origin Vite (`localhost:5173`) ditambahkan otomatis ke CSP saat
  ada `public/hot`.

### A8. Deteksi GPS spoofing sebagai flag, bukan pencegahan
- **APA**: `isSuspiciousTravel()` membandingkan koordinat absensi terakhir
  (haversine ÷ selisih waktu); kecepatan > **250 km/jam** → `is_anomaly_in/out`,
  ditampilkan badge "Anomali GPS" di daftar absensi admin.
- **KENAPA**: audit P2-8; spoofing sisi klien tak bisa dicegah 100% tanpa
  verifikasi kedua — flag adalah deteksi, bukan blokir.
- **TIDAK DIPILIH**: menolak absensi luar area / memblokir anomali otomatis.

### A9. Password reset & reset oleh admin memakai password acak
- **APA**: admin reset password memakai `Str::password(12, symbols:false)`,
  ditampilkan **sekali** di flash message (tidak pernah di log);
  reset user lewat OTP email.
- **KENAPA**: audit P1-6 (sebelumnya `password123`).
- **TIDAK DIPILIH**: password default yang bisa ditebak / dikirim via SMS.

## B. Bisnis & Model

### B1. Alur persetujuan berjenjang `pending → verified_by_admin → approved/rejected`
- **APA**: admin **memverifikasi** (menjaga kelengkapan/bukti), manajer memberi
  **keputusan final**. Admin boleh approve langsung dari `pending`.
- **KENAPA**: pembagian tugas sesuai peran di SPPG; verifikasi admin sebagai
  penyaring pertama.
- **TIDAK DIPILIH**: persetujuan tunggal (satu pihak) atau tiga tahap.
- **Catatan UX**: transisi yang tidak sah (mis. manajer menyetujui sebelum
  diverifikasi, atau pengajuan sudah diproses) mengembalikan flash error —
  bukan exception/error page.

### B2. Cuti disetujui → baris absensi otomatis + pengurangan kuota
- **APA**: `markLeaveDays()` membuat baris `attendances` (`izin`/`sakit`/`cuti`)
  per hari; `cuti` juga mengurangi `leave_balance`.
- **KENAPA**: laporan dan rekap karyawan jadi lengkap tanpa input manual;
  kuota dibatasi tiap tahun (`ensureLeaveQuota` reset ke `leave_quota`, default 12).
- **TIDAK DIPILIH**: mengubah status absensi manual / tanpa jejak audit.

### B3. 1 baris absensi per karyawan per hari (masuk + pulang)
- **APA**: kolom `time_in`/`time_out` (dan pasangan `method/photo/coord/is_outside/is_anomaly`)
  dalam satu baris `attendances`; unique `(employee_id, date)`.
- **KENAPA**: sederhana untuk rekap harian/bulanan dan mudah dibaca manusia;
  mencegah absensi ganda.
- **TIDAK DIPILIH**: dua baris terpisah (check-in/check-out events).

### B4. `Pengaturan` sebagai key-value ter-cache
- **APA**: tabel `settings` (key/value) diakses via `Pengaturan::get($key,$default)`
  yang di-cache 1 jam; `set()` menghapus cache. Termasuk `sppg_name`, koordinat,
  radius, `leave_quota`, `surat_counter`.
- **KENAPA**: konfigurasi runtime tanpa deploy; cache menghindari query berulang.
- **Catatan**: setelah `set()`, perubahan langsung terlihat; nilai koordinat/radius
  dipakai oleh geofencing absensi.

### B5. Tema gelap/terang kustom (bukan `dark:` Tailwind)
- **APA**: class `light` di `<html>` dari `localStorage('theme')`, dengan override
  CSS masif di `app.css` (selector `html:not(.light) body.panel-auth ...`) untuk
  welcome card, kartu statistik dasbor, dropdown profil, dsb.
- **KENAPA**: tema gelap dipakai default; pendekatan global CSS override
  lebih mudah diterapkan ke Blade legacy daripada refactor penuh ke `dark:`,
  dan menghindari `prefers-color-scheme` yang tidak bisa dikontrol user.
- **TIDAK DIPILIH**: `dark:` variant Tailwind + `darkMode: 'class'` (akan
  menyentuh banyak file dan rawan miss).

### B6. Konvensi penamaan campuran Indonesia/Inggris
- **APA**: tabel Inggris, model/controller campuran (Pengguna, Karyawan, Absensi…),
  route/view/fitur Indonesia, controller milik-pribadi berakhiran `*SayaController`.
- **KENAPA**: berkembang bertahap; tidak ada upaya standardisasi ulang — jangan
  "perbaiki" secara massal.

## C. Proses & Tooling

### C1. Test suite butuh build asset (Vite)
- **APA**: `@vite()` di layout → tanpa `public/build` (atau dev server),
  test render halaman gagal `ViteManifestNotFoundException`.
- **KENAPA**: konsekuensi penggunaan Vite plugin; bukan bug.
- **IMPLIKASI**: sebelum `php artisan test` / `composer test`, jalankan
  `npm run build` (atau dev server). Sudah diverifikasi dengan eksperimen.

### C2. Composer scripts & tooling
- `composer setup` — setup otomatis (install→env→key→migrate→npm→build).
- `composer dev` — serve + queue:listen + pail + vite (concurrently).
- `composer test` — `config:clear` + `artisan test`.
- `composer audit` — `composer audit` + `npm audit` (audit dependency, P2-9).
- `laravel/pao` di require-dev (tool dari Laravel untuk sample data — tidak
  dipakai di alur utama).

### C3. Referensi audit
Temuan & remediasi keamanan (11 item, P0–P2) terdokumentasi di `laporan.md`.
Skor audit: 6.5 → 9.0/10. Sisa risiko: GPS spoofing klien (terdeteksi via flag)
dan CSP `unsafe-inline`/`unsafe-eval` (kebutuhan Alpine/inline script).
