<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logos/favicon.png?v=3') }}">
        <title>Absen - {{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            #video-feed { width: 100%; max-height: 400px; border-radius: 0.75rem; background: #000; transform: scaleX(-1); }
            .capture-frame { position: absolute; inset: 12%; border: 3px dashed rgba(255,255,255,.8); border-radius: 50%; pointer-events: none; }
            #camera-holder { position: relative; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100 min-h-screen">
        <div class="max-w-md mx-auto p-4 space-y-4">

            @if (session('success'))
                <div class="px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
                <img src="{{ asset('images/logos/sppg-logo.png') }}" alt="Logo" class="w-16 h-16 mx-auto rounded-xl object-contain">
                <h1 class="mt-2 font-bold text-gray-800">{{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</h1>
                <p class="text-xs text-gray-400">Barcode Scan - {{ today()->translatedFormat('d F Y') }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-5" data-employee-badge data-name="{{ $employee->user?->name ?? '-' }}" data-nip="{{ $employee->nip }}" data-position="{{ $employee->position }}">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-blue-900 text-white flex items-center justify-center font-bold text-2xl">{{ strtoupper(substr($employee->user?->name ?? '-', 0, 1)) }}</div>
                    <div>
                        <div class="font-bold text-gray-800 text-lg">{{ $employee->user?->name ?? '-' }}</div>
                        <div class="text-sm text-gray-500">{{ $employee->nip }}</div>
                        <div class="text-xs text-gray-400">{{ $employee->position }} &middot; {{ $employee->workSchedule?->name }}</div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-gray-50 border p-3">
                        <div class="text-xs text-gray-500">Masuk</div>
                        <div class="font-bold text-lg {{ $todayAbsensi?->time_in ? 'text-blue-900' : 'text-gray-300' }}">{{ $todayAbsensi?->time_in?->format('H:i:s') ?? 'Belum' }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 border p-3">
                        <div class="text-xs text-gray-500">Pulang</div>
                        <div class="font-bold text-lg {{ $todayAbsensi?->time_out ? 'text-blue-900' : 'text-gray-300' }}">{{ $todayAbsensi?->time_out?->format('H:i:s') ?? 'Belum' }}</div>
                    </div>
                </div>
            </div>

            <div id="loc-status" class="rounded-xl bg-white shadow-sm px-4 py-3 text-sm text-gray-500 border-l-4 border-gray-300 flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-300 shrink-0" id="loc-dot"></span>
                <span id="loc-text" class="flex-1">Mendeteksi lokasi...</span>
                <button type="button" onclick="updateLocation()" class="text-xs font-semibold text-blue-700 hover:text-blue-900 shrink-0">Deteksi Ulang</button>
            </div>

            @if (! $todayAbsensi || ! $todayAbsensi->time_out)
                <div class="bg-white rounded-2xl shadow-sm p-5">
                    <button id="with-selfie" class="mb-3 flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" id="with-selfie-check" class="rounded text-blue-900" checked> Ambil foto wajah sebagai bukti
                    </button>

                    <div id="selfie-area" class="hidden mb-3">
                        <video id="selfie-video" class="w-full rounded-lg bg-black" autoplay playsinline muted></video>
                        <button type="button" onclick="captureSelfie()" class="mt-2 w-full py-2.5 rounded-lg bg-gray-800 text-white text-sm font-semibold">Ambil Foto</button>
                        <img id="selfie-preview" class="hidden mt-2 w-full rounded-lg" alt="Preview">
                    </div>

                    <div id="camera-holder" class="hidden">
                        <video id="video-feed" autoplay playsinline muted></video>
                        <div class="capture-frame"></div>
                    </div>

                    <button type="button" onclick="toggleCamera()" id="camera-toggle-btn" class="w-full py-3 rounded-xl border-2 border-blue-900 text-blue-900 font-bold hover:bg-blue-50">
                        <span class="inline-flex items-center gap-2 justify-center"><x-ikon name="camera" class="w-5 h-5" /> Absen Pakai Foto Kamera</span>
                    </button>
                    <button type="button" onclick="clockIn()" id="clock-in-btn" class="mt-2 w-full py-3 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-bold">
                        <span class="inline-flex items-center gap-2 justify-center"><x-ikon name="check" class="w-5 h-5" /> Konfirmasi Absen ({{ $todayAbsensi ? 'Pulang' : 'Masuk' }})</span>
                    </button>
                </div>
            @endif

            <form method="POST" action="{{ route('karyawan.absen.barcode') }}" id="submit-form" class="hidden">
                @csrf
                <input type="hidden" name="code" value="{{ $barcode->code }}">
                <input type="hidden" name="photo" id="photo">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </form>

            @if (session('clockResult'))
                @php $r = session('clockResult'); @endphp
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-6" onclick="this.remove()">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center" onclick="event.stopPropagation()">
                        <div class="mb-3 flex items-center justify-center">
                            <div class="w-16 h-16 rounded-full {{ $r['type'] === 'in' ? 'bg-blue-900' : 'bg-blue-950' }} text-white flex items-center justify-center">
                                <x-ikon name="{{ $r['type'] === 'in' ? 'check' : 'log-out' }}" class="w-8 h-8" />
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Absen {{ $r['type'] === 'in' ? 'Masuk' : 'Pulang' }} Berhasil</h3>
                        <div class="text-3xl font-bold text-blue-900">{{ $r['time'] }}</div>
                        @if (isset($r['status']))
                            <div class="mt-2 inline-block px-3 py-1 rounded-full text-sm font-bold {{ $r['status'] === 'telat' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $r['status'] === 'telat' ? 'TELAT' : 'HADIR' }}</div>
                        @endif
                        <div class="mt-3 text-sm {{ $r['outside'] ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} rounded-lg px-3 py-2 flex items-center gap-2">
                            @if ($r['outside'])
                                <x-ikon name="alert" class="w-4 h-4 shrink-0" />
                                Di luar area SPPG ({{ $r['distance'] }} m)
                            @else
                                Lokasi dalam area ({{ $r['distance'] }} m)
                            @endif
                        </div>
                        <button onclick="this.closest('.fixed').remove()" class="mt-4 w-full py-2.5 rounded-xl bg-blue-900 text-white font-semibold">Tutup</button>
                    </div>
                </div>
            @endif
        </div>

        <script>
            const SPPG = { lat: {{ \App\Models\Pengaturan::get('sppg_latitude', '0') }}, lng: {{ \App\Models\Pengaturan::get('sppg_longitude', '0') }}, radius: {{ $radius }} };
            let loc = null;
            let camStream = null;
            let selfieStream = null;
            let photoBase64 = null;

            function haversine(lat1, lon1, lat2, lon2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
                return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }

            function setLoc(text, color) {
                document.getElementById('loc-dot').className = 'inline-block w-2.5 h-2.5 rounded-full mr-2 ' + color;
                document.getElementById('loc-text').textContent = text;
            }

            function isSecureCtx() {
                if (window.isSecureContext === false) {
                    const msg = 'Halaman tidak diakses melalui HTTPS. Lokasi & kamera hanya berfungsi di https:// atau http://localhost.';
                    setLoc(msg, 'bg-red-500');
                    alert(msg);
                    return false;
                }
                return true;
            }

            function getLocation(timeout = 30000, highAccuracy = true) {
                return new Promise((resolve, reject) => {
                    if (!navigator.geolocation) {
                        reject(new Error('unsupported'));
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                        (err) => reject(err),
                        { enableHighAccuracy: highAccuracy, timeout, maximumAge: 0 }
                    );
                });
            }

            function getPermissionState() {
                if (navigator.permissions && navigator.permissions.query) {
                    return navigator.permissions.query({ name: 'geolocation' })
                        .then((s) => s.state)
                        .catch(() => null);
                }
                return Promise.resolve(null);
            }

            function deniedMessage() {
                const isChrome = /Chrome/i.test(navigator.userAgent) && !/Edg/i.test(navigator.userAgent);
                const isAndroid = /Android/i.test(navigator.userAgent);
                const isIos = /iPhone|iPad/i.test(navigator.userAgent);
                if (isAndroid && isChrome) {
                    return 'Izin lokasi diblokir. Buka menu titik tiga (â‹®) > Pengaturan situs > Lokasi > ubah ke "Izinkan", lalu muat ulang halaman.';
                }
                if (isIos) {
                    return 'Izin lokasi diblokir. Buka Pengaturan (Pengaturans) > Safari > Lokasi > "Saat Mengunjungi" atau izinkan di pengaturan situs, lalu muat ulang.';
                }
                return 'Izin lokasi diblokir. Klik ikon gembok/â„¹ di samping alamat URL > izinkan Lokasi (Location) untuk situs ini, lalu muat ulang halaman.';
            }

            function setLocStatus(p) {
                const dist = haversine(p.lat, p.lng, SPPG.lat, SPPG.lng);
                const inside = dist <= SPPG.radius;
                setLoc(`Lokasi: ${Math.round(dist)} m dari SPPG - ${inside ? 'DALAM AREA âœ“' : 'LUAR AREA âš ' }`, inside ? 'bg-emerald-500' : 'bg-red-500');
            }

            function locErrorMessage(err) {
                if (!err || err.message === 'unsupported') return 'Geolokasi tidak didukung browser ini.';
                if (err.code === 1) return 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan browser/HP, lalu tekan Deteksi Ulang.';
                if (err.code === 2) return 'Sinyal GPS tidak tersedia. Coba di area terbuka, lalu tekan Deteksi Ulang.';
                if (err.code === 3) return 'Waktu deteksi lokasi habis. Nyalakan GPS & koneksi internet, lalu tekan Deteksi Ulang.';
                return 'Gagal mengambil lokasi: ' + err.message;
            }

            async function ensureLocation() {
                if (loc) return loc;
                if (!isSecureCtx()) return null;

                const state = await getPermissionState();
                if (state === 'denied') {
                    const msg = deniedMessage();
                    setLoc(msg, 'bg-red-500');
                    alert(msg);
                    return null;
                }

                setLoc('Mendeteksi lokasi... aktifkan GPS & tunggu sebentar', 'bg-amber-500');
                try {
                    loc = await getLocation(30000, true);
                } catch (err1) {
                    if (err1 && err1.code === 1 && state !== 'granted') {
                        const msg = deniedMessage();
                        setLoc(msg, 'bg-red-500');
                        alert(msg);
                        return null;
                    }
                    try {
                        loc = await getLocation(20000, false);
                    } catch (err) {
                        const msg = locErrorMessage(err);
                        setLoc(msg, 'bg-red-500');
                        alert(msg);
                        return null;
                    }
                }
                setLocStatus(loc);
                return loc;
            }

            async function updateLocation() {
                await ensureLocation();
            }

            updateLocation();

            // selfie
            document.getElementById('with-selfie-check').addEventListener('change', (e) => {
                document.getElementById('selfie-area').classList.toggle('hidden', !e.target.checked);
                if (e.target.checked && !photoBase64) startSelfie();
                else stopSelfie();
            });

            async function startSelfie() {
                try {
                    selfieStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                    const v = document.getElementById('selfie-video');
                    v.srcObject = selfieStream;
                    await v.play();
                } catch (e) { alert('Gagal akses kamera: ' + e.message); }
            }

            function captureSelfie() {
                const v = document.getElementById('selfie-video');
                const c = document.createElement('canvas');
                c.width = v.videoWidth || 640;
                c.height = v.videoHeight || 480;
                c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
                photoBase64 = c.toDataURL('image/jpeg', 0.7);
                document.getElementById('selfie-preview').src = photoBase64;
                document.getElementById('selfie-preview').classList.remove('hidden');
                stopSelfie();
            }

            function stopSelfie() {
                if (selfieStream) { selfieStream.getTracks().forEach(t => t.stop()); selfieStream = null; }
                document.getElementById('selfie-video').srcObject = null;
            }

            // kamera penuh
            async function toggleCamera() {
                const holder = document.getElementById('camera-holder');
                const btn = document.getElementById('camera-toggle-btn');
                if (camStream) {
                    camStream.getTracks().forEach(t => t.stop());
                    camStream = null;
                    holder.classList.add('hidden');
                    btn.textContent = 'Absen Pakai Foto Kamera';
                    return;
                }
                try {
                    camStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                    const v = document.getElementById('video-feed');
                    v.srcObject = camStream;
                    await v.play();
                    holder.classList.remove('hidden');
                    btn.textContent = 'Matikan Kamera';
                } catch (e) { alert('Gagal akses kamera: ' + e.message); }
            }

            async function clockIn() {
                if (!loc) {
                    alert('Lokasi belum terdeteksi. Mencoba mendeteksi ulang, tunggu sebentar...');
                    const p = await ensureLocation();
                    if (!p) return;
                }
                const form = document.getElementById('submit-form');
                if (camStream) {
                    const v = document.getElementById('video-feed');
                    const c = document.createElement('canvas');
                    c.width = v.videoWidth || 640;
                    c.height = v.videoHeight || 480;
                    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
                    photoBase64 = c.toDataURL('image/jpeg', 0.75);
                }
                document.getElementById('photo').value = photoBase64 || '';
                document.getElementById('latitude').value = loc.lat;
                document.getElementById('longitude').value = loc.lng;
                form.submit();
            }

            window.addEventListener('beforeunload', () => {
                if (camStream) camStream.getTracks().forEach(t => t.stop());
                if (selfieStream) selfieStream.getTracks().forEach(t => t.stop());
            });
        </script>
    </body>
</html>
