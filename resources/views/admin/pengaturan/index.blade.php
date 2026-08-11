@extends('tata-letak.sidebar')

@section('title', 'Pengaturan - ' . config('app.name'))
@section('page-title', 'Pengaturan Sistem')

@section('content')
    <div class="max-w-2xl space-y-5">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-1">Profil SPPG</h3>
            <p class="text-sm text-gray-400 mb-5">Data ini dipakai untuk nama instansi, alamat, dan validasi radius lokasi absensi.</p>
            <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-semibold text-gray-500">Nama SPPG *</label>
                    <input type="text" name="sppg_name" value="{{ old('sppg_name', $settings['sppg_name'] ?? '') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Alamat Lengkap *</label>
                    <textarea name="sppg_address" rows="2" required class="mt-1 w-full rounded-lg border-gray-300">{{ old('sppg_address', $settings['sppg_address'] ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Latitude Titik SPPG *</label>
                        <input type="text" name="sppg_latitude" value="{{ old('sppg_latitude', $settings['sppg_latitude'] ?? '') }}" required class="mt-1 w-full rounded-lg border-gray-300" placeholder="mis. -2.9921000">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Longitude Titik SPPG *</label>
                        <input type="text" name="sppg_longitude" value="{{ old('sppg_longitude', $settings['sppg_longitude'] ?? '') }}" required class="mt-1 w-full rounded-lg border-gray-300" placeholder="mis. 120.1962000">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Radius Absensi (meter) *</label>
                        <input type="number" name="radius_meter" value="{{ old('radius_meter', $settings['radius_meter'] ?? '100') }}" min="10" max="10000" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Toleransi Telat Default (menit) *</label>
                        <input type="number" name="default_tolerance_minutes" value="{{ old('default_tolerance_minutes', $settings['default_tolerance_minutes'] ?? '15') }}" min="0" max="180" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Jatah Cuti Tahunan (hari) *</label>
                        <input type="number" name="leave_quota" value="{{ old('leave_quota', $settings['leave_quota'] ?? '12') }}" min="0" max="365" required class="mt-1 w-full rounded-lg border-gray-300">
                        <p class="mt-1 text-xs text-gray-400">Kuota per karyawan, direset tiap awal tahun.</p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3 space-y-2">
                    <div class="flex items-start gap-2 text-xs text-gray-500">
                        <x-ikon name="map-pin" class="w-4 h-4 shrink-0 mt-0.5 text-blue-700" />
                        <span><b>Ambil otomatis dari link Google Maps:</b> salin URL peta lokasi SPPG di bawah, lalu klik "Ambil Koordinat". Bisa juga tempel teks koordinat langsung (mis. -2.9921, 120.1962).</span>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" id="gmaps-link" class="flex-1 rounded-lg border-gray-300 text-sm" placeholder="https://maps.app.goo.gl/... atau https://www.google.com/maps/@-2.9921,120.1962,17z">
                        <button type="button" onclick="extractCoords()" class="px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold shrink-0">Ambil Koordinat</button>
                    </div>
                </div>
                <button class="w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Simpan Pengaturan</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-1">Kepala SPPG</h3>
            <p class="text-sm text-gray-400 mb-5">Nama dan NIP Kepala SPPG ini otomatis tercantum pada PDF laporan yang dihasilkan dari menu Laporan.</p>
            <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-semibold text-gray-500">Nama Kepala SPPG</label>
                    <input type="text" name="kepala_nama" value="{{ old('kepala_nama', $settings['kepala_nama'] ?? '') }}" class="mt-1 w-full rounded-lg border-gray-300" placeholder="mis. Dra. Hj. Siti Nurhaliza, M.Pd.">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">NIP Kepala SPPG</label>
                    <input type="text" name="kepala_nip" value="{{ old('kepala_nip', $settings['kepala_nip'] ?? '') }}" class="mt-1 w-full rounded-lg border-gray-300" placeholder="mis. 197001011990032001">
                </div>
                <button class="w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Simpan Data</button>
            </form>
        </div>

        <script>
            function extractCoords() {
                const raw = document.getElementById('gmaps-link').value.trim();
                if (!raw) { showAlert.error('Tempel dulu link Google Maps atau koordinatnya.'); return; }

                const patterns = [
                    /@(-?\d{1,3}(?:\.\d+)?),\s*(-?\d{1,3}(?:\.\d+)?)/,
                    /[?&](?:q|ll|d)=(-?\d{1,3}(?:\.\d+)?)[,|%2C]\s*(-?\d{1,3}(?:\.\d+)?)/,
                    /^(-?\d{1,3}(?:\.\d+)?)[,\s]\s*(-?\d{1,3}(?:\.\d+)?)$/,
                ];

                let lat = null, lng = null;
                for (const p of patterns) {
                    const m = raw.match(p);
                    if (m) { lat = m[1]; lng = m[2]; break; }
                }

                if (lat === null || lng === null) {
                    showAlert.error('Koordinat tidak ditemukan di link tersebut. Buka link-nya, salin URL dari address bar (format @lat,lng), lalu coba lagi.');
                    return;
                }

                lat = parseFloat(lat);
                lng = parseFloat(lng);
                if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                    showAlert.error('Nilai koordinat tidak valid.');
                    return;
                }

                document.querySelector('input[name="sppg_latitude"]').value = lat;
                document.querySelector('input[name="sppg_longitude"]').value = lng;
                showAlert.success('Koordinat terisi. Klik "Simpan Pengaturan" untuk menyimpan.');
            }
        </script>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4">Akun Login Awal (hasil seeder)</h3>
            <table class="text-sm">
                <tbody class="divide-y divide-gray-100">
                    <tr><td class="py-2 pr-6 font-semibold">Admin</td><td class="py-2 pr-6 font-mono text-xs">admin@absensi.sppg.id</td><td class="py-2 font-mono text-xs text-gray-400">password123</td></tr>
                    <tr><td class="py-2 pr-6 font-semibold">Manajer</td><td class="py-2 pr-6 font-mono text-xs">manajer@absensi.sppg.id</td><td class="py-2 font-mono text-xs text-gray-400">password123</td></tr>
                    <tr><td class="py-2 pr-6 font-semibold">Karyawan</td><td class="py-2 pr-6 font-mono text-xs">karyawan@absensi.sppg.id</td><td class="py-2 font-mono text-xs text-gray-400">password123</td></tr>
                </tbody>
            </table>
            <p class="text-xs text-red-500 mt-3 font-semibold inline-flex items-center gap-1.5"><x-ikon name="alert" class="w-4 h-4" /> Segera ganti password akun-akun ini sebelum dipakai produksi!</p>
        </div>
    </div>
@endsection
