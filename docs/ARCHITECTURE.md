# Arsitektur — SPPG Palopo (Sistem Absensi Digital)

Dokumen ini memetakan role → akses, alur bisnis multi-tahap (state machine),
dan relasi model utama. Semua mengacu pada source code aktual
(`routes/web.php`, `routes/auth.php`, `app/Http/Controllers/**`,
`app/Models/**`).

## 1. Peta Role & Akses

Role disimpan di tabel `roles` dan dicek lewat middleware kustom `peran:...`
(`app/Http/Middleware/PastikanPeran`), berdasar **nama** role.

### 1.1 Route publik (tanpa login)

| Route | Fungsi |
|---|---|
| `GET /` | Landing page (`beranda`) |
| `GET /scan/{code}` | Halaman absen publik dari scan QR kartu (constraint `[A-Za-z0-9-]{8,}`) |
| `POST /absen/barcode` (`throttle:10,1`) | Proses absen via barcode (publik) |
| `GET /sppg-info` | JSON info SPPG (nama/alamat/koordinat/radius) |
| `GET /register`, `POST /register` | Registrasi publik → akun `pending` |
| `GET/POST /login`, `POST /logout` | Login/logout |
| `forgot-password/*` | Reset password via OTP email |

### 1.2 Auth (Breeze yang dimodifikasi besar)

- Login `POST /login` → `throttle:10,1` + limiter per email+IP di
  `app/Http/Requests/Auth/PermintaanMasuk.php`.
- **Email verification via OTP** (bukan link): route `verifikasi-otp`
  (`VerifikasiOtpController`). Route `/dasbor` di-proteksi
  `verified:verifikasi-otp`. Kode OTP 6 digit di-hash di DB, kedaluwarsa
  10 menit (`Pengguna::generateEmailOtp` / `verifyEmailOtp`).
- Registrasi publik → `status='pending'`, **tanpa auto-login & tanpa OTP**,
  menunggu aktivasi admin (`PenggunaTerdaftarController::store`).
- Reset password → `forgot-password` kirim OTP → `password.otp` masukkan OTP →
  atur sandi baru (`TautanResetSandiController` + `SandiOtpController`,
  pakai session `reset_email` + `reset_otp_terverifikasi`).

### 1.3 Panel terautentikasi (group `auth`)

Prefix + middleware per role:

| Group | Prefix | Middleware | Fungsi utama |
|---|---|---|---|
| Karyawan | `/karyawan` | `peran:karyawan` | absen, QR sendiri, riwayat, rekap, cuti/lembur/koreksi (pengajuan sendiri) |
| Admin | `/admin` | `peran:admin` | kelola karyawan, jadwal kerja, jabatan, barcode, absensi, pengaturan, pengguna, log audit, laporan, approval |
| Manajer | `/manajer` | `peran:manajer` | monitoring absensi, persetujuan final pengajuan, laporan; **karyawan hanya lihat** |

Catatan penting (perubahan terbaru): manajer **tidak** punya route
tambah/edit/hapus karyawan (`manajer/karyawan` hanya `index`). Tombol aksi di
`admin/karyawan/index.blade.php` (Tambah/Edit/Hapus/Cetak barcode) dibungkus
`@if (auth()->user()->isAdmin())`.

## 2. Alur Bisnis Multi-Tahap (State Machine)

### 2.1 Alur persetujuan pengajuan (cuti/izin/sakit, lembur, koreksi)

Konsisten untuk tiga entitas: `PengajuanCuti`, `Lembur`, `KoreksiAbsensi`
(controller: `Admin\PersetujuanCutiController`, `Admin\PersetujuanLemburController`,
`Admin\PersetujuanKoreksiController`).

```
pending ──(admin: verify)──▶ verified_by_admin ──(manajer: approve/reject)──▶ approved / rejected
   │                                                                              
   └──────(admin: approve langsung)──────▶ approved / rejected
```

Aturan aktual:
- Karyawan membuat pengajuan → `status = pending` (default).
- **Admin**: bisa `verify` (pending → `verified_by_admin`), atau `approve`/`reject`
  langsung dari `pending` maupun `verified_by_admin`.
- **Manajer**: hanya boleh `approve`/`reject` jika status `verified_by_admin`.
  Sebelum diverifikasi admin → ditolak dengan pesan error (flash), bukan error page.
- Semua transisi menyimpan `approved_by` + `approved_at` dan menulis audit log.

Efek samping saat `approve`:
- **Cuti/izin/sakit**: `markLeaveDays()` membuat baris `attendances` berstatus
  `izin`/`sakit`/`cuti` untuk tiap hari (tidak menimpa baris absensi yang sudah ada).
- **Cuti**: `deductLeaveQuota()` mengurangi `leave_balance` karyawan.
- **Koreksi**: `applyCorrection()` update/insert `attendances` dengan jam baru
  (status `hadir`).
- **Lembur**: hanya update status (jumlah menit `duration_minutes` dihitung saat
  karyawan membuat pengajuan).

### 2.2 Alur absensi masuk/pulang (per hari, 1 baris)

```
karyawan ──▶ POST /karyawan/absen/kamera (auth)  ─┐
           ─▶ POST /absen/barcode (publik, scan) ─┴─▶ doClockInOut()
```

`AbsensiController::doClockInOut`:
1. Ambil absensi hari ini; jika belum ada → buat baris `time_in` (masuk).
   Status `hadir`, atau `telat` jika waktu melewati
   `time_in` jadwal + `tolerance_minutes` (default dari `Pengaturan::get('default_tolerance_minutes', '15')`).
2. Jika sudah ada `time_in` dan belum `time_out` → isi `time_out` (pulang).
3. Jika sudah `time_out` → respon "sudah absen masuk & pulang".
4. Setiap langkah mencatat `method_*`, `photo_*`, `latitude_*/longitude_*`,
   `is_outside_area_*` (geofencing), `is_anomaly_*` (deteksi GPS spoofing).

Foto diproses `storeBase64Photo()`: validasi `finfo` MIME (JPEG/PNG) + re-encode
GD (kualitas 85), disimpan ke `storage/app/public/attendance/YYYY/MM/DD/`.

Deteksi anomali GPS (`isSuspiciousTravel`): kecepatan antar-rekaman absensi
> 250 km/jam → flag `is_anomaly_in/out`.

### 2.3 Alur registrasi & aktivasi akun

```
POST /register ──▶ users.status = 'pending' (tanpa auto-login)
admin: /admin/pengguna toggle-status ──▶ users.status = 'active'
login (password) ──▶ verifikasi email via OTP (jika belum) ──▶ /dasbor
```

### 2.4 Alur reset password

```
forgot-password (email) ──▶ kirim OTP ──▶ /forgot-password/verifikasi
  (masukkan OTP, session reset_otp_terverifikasi) ──▶ atur sandi baru ──▶ login
```

### 2.5 Alur kuota cuti tahunan

`Karyawan::ensureLeaveQuota()`: bila `leave_balance_year !== tahun berjalan`,
reset `leave_balance` ke `Pengaturan::get('leave_quota', '12')`. Dipanggil saat
membaca `sisa_cuti`, saat pengajuan cuti (validasi kuota), dan saat cuti
disetujui (pengurangan).

### 2.6 Laporan (harian/bulanan/tahunan)

`Admin\LaporanController::index` — filter periode → export `xlsx`/`csv`
(`LaporanAbsensiExport`, PhpSpreadsheet) atau `print`/`pdf`
(`admin/laporan/pdf.blade.php`, DomPDF). Nomor surat dibuat dari
`Pengaturan::get('surat_counter')` yang di-increment tiap export/cetak.

## 3. Relasi Antar Model

```
roles (Peran) 1──∞ users (Pengguna)      [users.role_id → roles.id]
users 1──1 employees (Karyawan)          [employees.user_id → users.id]

employees (Karyawan) ∞──1 work_schedules (JadwalKerja)   [employees.work_schedule_id]
employees ∞──1 positions (Jabatan)                        [employees.position_id]
employees 1──∞ barcodes (Barcode)                        [barcodes.employee_id]
employees 1──∞ attendances (Absensi)                     [attendances.employee_id]
employees 1──∞ leave_requests (PengajuanCuti)            [leave_requests.employee_id]
employees 1──∞ overtime_requests (Lembur)                [overtime_requests.employee_id]
employees 1──∞ attendance_corrections (KoreksiAbsensi)   [attendance_corrections.employee_id]

attendances (Absensi) ∞──1 work_schedules                [attendances.work_schedule_id]
attendance_corrections ∞──1 attendances                  [attendance_corrections.attendance_id] (nullable)

leave_requests / overtime_requests / attendance_corrections
   ∞──1 users (approver)                                 [*.approved_by → users.id] (nullable)

users 1──∞ audit_logs (LogAudit)                         [audit_logs.user_id] (nullable)
```

### Accessor & bantuan model penting

- `Pengguna`: `isAdmin()/isManajer()/isKaryawan()` (cek nama role),
  `generateEmailOtp()`, `verifyEmailOtp()`, relasi `role`, `employee`.
- `Karyawan`: `user`, `workSchedule`, `jabatan`, `barcodes`, `activeBarcode`
  (HasOne latest aktif & belum kedaluwarsa), `attendances`, `leaveRequests`,
  `overtimeRequests`, `attendanceCorrections`, `sisa_cuti` (accessor),
  `ensureLeaveQuota()`.
- `Absensi`: cast `date`, `time_in/out` datetime, flag boolean, `statusLabel`.
- `PengajuanCuti` / `Lembur` / `KoreksiAbsensi`: relasi `employee`, `approver`;
  accessor `statusLabel` (`pending | verified_by_admin | approved | rejected`).
- `Pengaturan`: helper statis `get($key,$default)` (cache 1 jam) & `set($key,$value)`.
