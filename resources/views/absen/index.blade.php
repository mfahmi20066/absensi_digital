@extends('tata-letak.sidebar')

@section('title', 'Absen - ' . config('app.name'))
@section('page-title', 'Absen Masuk / Pulang')

@push('styles')
    <style>
        #scanner video { width: 100% !important; border-radius: 0.75rem; }
        #scanner { border-radius: 0.75rem; overflow: hidden; }
        .scanner-placeholder { aspect-ratio: 4/3; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border-radius: 0.75rem; }
        #video-feed { width: 100%; max-height: 380px; border-radius: 0.75rem; background: #000; transform: scaleX(-1); }
        #camera-holder { position: relative; }
        .capture-frame { position: absolute; inset: 10%; border: 3px dashed rgba(255,255,255,.8); border-radius: 50%; pointer-events: none; }
    </style>
@endpush

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/logos/sppg-logo.png') }}" alt="Logo" class="w-14 h-14 rounded-xl object-contain">
                    <div>
                        <h2 class="font-bold text-gray-800">{{ auth()->user()->name }}</h2>
                        <div class="text-sm text-gray-500">{{ $employee->nip }} &middot; {{ $employee->position }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $schedule?->name }} ({{ $schedule?->time_in?->format('H:i') }} - {{ $schedule?->time_out?->format('H:i') }})</div>
                    </div>
                </div>
                <div class="text-right">
                    <div id="clock" class="text-2xl font-bold text-blue-900 tabular-nums">--:--:--</div>
                    <div class="text-xs text-gray-400">{{ today()->translatedFormat('d F Y') }}</div>
                </div>
            </div>

            @if ($todayAbsensi)
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl {{ $todayAbsensi->time_in ? 'bg-blue-50 border-blue-200' : 'bg-gray-50' }} border p-4">
                        <div class="text-xs text-gray-500">Absen Masuk</div>
                        <div class="text-xl font-bold {{ $todayAbsensi->time_in ? 'text-blue-900' : 'text-gray-300' }}">{{ $todayAbsensi->time_in?->format('H:i:s') ?? 'Belum' }}</div>
                        <div class="text-xs text-gray-400 mt-1">
                            {{ $todayAbsensi->method_in ?? '-' }}
                            @if ($todayAbsensi->is_outside_area_in)
                                <span class="ml-1 inline-block px-1.5 py-0.5 rounded bg-red-100 text-red-600 font-semibold">LUAR AREA</span>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-xl {{ $todayAbsensi->time_out ? 'bg-blue-50 border-blue-200' : 'bg-gray-50' }} border p-4">
                        <div class="text-xs text-gray-500">Absen Pulang</div>
                        <div class="text-xl font-bold {{ $todayAbsensi->time_out ? 'text-blue-900' : 'text-gray-300' }}">{{ $todayAbsensi->time_out?->format('H:i:s') ?? 'Belum' }}</div>
                        <div class="text-xs text-gray-400 mt-1">
                            {{ $todayAbsensi->method_out ?? '-' }}
                            @if ($todayAbsensi->is_outside_area_out)
                                <span class="ml-1 inline-block px-1.5 py-0.5 rounded bg-red-100 text-red-600 font-semibold">LUAR AREA</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if ($todayAbsensi->time_out)
                    <div class="mt-4 px-4 py-3 rounded-xl bg-gray-100 text-gray-600 text-sm text-center">Anda sudah absen masuk & pulang hari ini. Sampai jumpa besok!</div>
                @endif
            @endif
        </div>

        <div id="loc-status" class="rounded-xl shadow-sm px-4 py-3 text-sm bg-white border-l-4 border-gray-300 text-gray-500">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-300" id="loc-dot"></span>
                <span id="loc-text" class="flex-1">Menunggu izin akses lokasi...</span>
                <button type="button" onclick="updateLocation()" class="text-xs font-semibold text-blue-700 hover:text-blue-900 shrink-0">Deteksi Ulang</button>
            </div>
        </div>

        @if (! $todayAbsensi || ! $todayAbsensi->time_out)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="grid grid-cols-2 gap-2 mb-5">
                    <button id="tab-barcode" class="tab-btn py-3 rounded-xl font-semibold text-sm bg-blue-900 text-white" onclick="switchTab('barcode')">Scan Barcode</button>
                    <button id="tab-camera" class="tab-btn py-3 rounded-xl font-semibold text-sm bg-gray-100 text-gray-600" onclick="switchTab('camera')">Absen Kamera</button>
                </div>

                <div id="panel-barcode">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-gray-800">Scan Barcode Karyawan</h3>
                        <button type="button" onclick="stopScanner()" class="text-xs text-gray-500 hover:text-red-600 hidden" id="stop-scan-btn">Hentikan kamera</button>
                    </div>
                    <div id="scanner">
                        <div class="scanner-placeholder">
                            <div class="text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5v14m4-14v14m4-14v14m4-14v14m4-14v14"/></svg>
                                <p class="text-sm">Kamera akan aktif untuk memindai barcode.</p>
                                <button type="button" onclick="startScanner()" class="mt-3 inline-block px-4 py-2 rounded-lg bg-blue-900 text-white text-sm font-semibold hover:bg-blue-950">Aktifkan Kamera</button>
                            </div>
                        </div>
                    </div>

                    <div id="scan-result" class="mt-4 hidden">
                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-blue-900 text-white flex items-center justify-center font-bold text-lg">{{ strtoupper(substr('', 0, 1)) }}</div>
                                <div>
                                    <div id="scan-name" class="font-bold text-gray-800"></div>
                                    <div id="scan-nip" class="text-xs text-gray-500"></div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('karyawan.absen.barcode') }}" id="barcode-form" class="mt-3 space-y-3">
                                @csrf
                                <input type="hidden" name="code" id="scan-code">
                                <input type="hidden" name="latitude" id="lat-barcode">
                                <input type="hidden" name="longitude" id="lng-barcode">
                                <input type="hidden" name="photo" id="photo-barcode">
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="checkbox" id="with-selfie-barcode" class="rounded text-blue-900" checked> Ambil foto wajah sebagai bukti
                                </label>
                                <div id="barcode-selfie-wrap" class="hidden">
                                    <video id="selfie-barcode-video" class="w-full rounded-lg bg-black" autoplay playsinline muted></video>
                                    <button type="button" onclick="captureSelfie('barcode')" class="mt-2 w-full py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold">Ambil Foto</button>
                                    <canvas id="canvas-barcode" class="hidden"></canvas>
                                    <img id="preview-barcode" class="hidden mt-2 w-full rounded-lg" alt="Preview foto">
                                </div>
                                <button type="submit" class="w-full py-3 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-bold">Konfirmasi Absen</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="panel-camera" class="hidden">
                    <h3 class="font-bold text-gray-800 mb-3">Absen dengan Foto Kamera</h3>
                    <div id="camera-holder">
                        <video id="video-feed" autoplay playsinline muted></video>
                        <div class="capture-frame"></div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="button" onclick="startCamera()" class="flex-1 py-2.5 rounded-lg bg-gray-800 text-white text-sm font-semibold" id="cam-start-btn">Nyalakan Kamera</button>
                        <button type="button" onclick="captureCameraPhoto()" class="flex-1 py-2.5 rounded-lg bg-blue-900 text-white text-sm font-semibold" id="cam-capture-btn" disabled>Ambil Foto & Absen</button>
                    </div>
                    <form method="POST" action="{{ route('karyawan.absen.kamera') }}" id="camera-form" class="hidden">
                        @csrf
                        <input type="hidden" name="photo" id="photo-camera">
                        <input type="hidden" name="latitude" id="lat-camera">
                        <input type="hidden" name="longitude" id="lng-camera">
                    </form>
                </div>
            </div>
        @endif

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
                    <div class="text-3xl font-bold text-blue-900 tabular-nums">{{ $r['time'] }}</div>
                    @if (isset($r['status']))
                        <div class="mt-2 inline-block px-3 py-1 rounded-full text-sm font-bold {{ $r['status'] === 'telat' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $r['status'] === 'telat' ? 'TELAT' : 'HADIR' }}
                        </div>
                    @endif
                    @if ($r['outside'])
                        <div class="mt-3 text-sm bg-red-50 text-red-700 rounded-lg px-3 py-2 flex items-center gap-2"><x-ikon name="alert" class="w-4 h-4 shrink-0" /> Anda absen di luar area SPPG ({{ $r['distance'] }} m)</div>
                    @else
                        <div class="mt-3 text-sm bg-emerald-50 text-emerald-700 rounded-lg px-3 py-2">Lokasi dalam area SPPG ({{ $r['distance'] }} m)</div>
                    @endif
                    <button onclick="this.closest('.fixed').remove()" class="mt-4 w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Tutup</button>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        const SPPG = { lat: {{ \App\Models\Pengaturan::get('sppg_latitude', '0') }}, lng: {{ \App\Models\Pengaturan::get('sppg_longitude', '0') }}, radius: {{ $radius }} };
        let loc = null;
        let html5Qr = null;
        let camStream = null;
        let selfieStream = null;

        // ===== Jam realtime =====
        function tick() {
            const el = document.getElementById('clock');
            if (el) el.textContent = new Date().toLocaleTimeString('id-ID');
        }
        tick();
        setInterval(tick, 1000);

        // ===== Geolokasi =====
        function isSecureCtx() {
            if (window.isSecureContext === false) {
                const msg = 'Halaman tidak diakses melalui HTTPS. Lokasi & kamera hanya berfungsi di https:// atau http://localhost.';
                setLoc(msg, 'bg-red-500');
                showAlert.error(msg);
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
                return 'Izin lokasi diblokir. Buka menu titik tiga (⋮) > Pengaturan situs > Lokasi > ubah ke "Izinkan", lalu muat ulang halaman.';
            }
            if (isIos) {
                return 'Izin lokasi diblokir. Buka Pengaturan (Pengaturans) > Safari > Lokasi > "Saat Mengunjungi" atau izinkan di pengaturan situs, lalu muat ulang.';
            }
            return 'Izin lokasi diblokir. Klik ikon gembok/ℹ di samping alamat URL > izinkan Lokasi (Location) untuk situs ini, lalu muat ulang halaman.';
        }

        function setLocStatus(p) {
            const dist = haversine(p.lat, p.lng, SPPG.lat, SPPG.lng);
            const inside = dist <= SPPG.radius;
            setLoc(`Lokasi terdeteksi (${p.lat.toFixed(5)}, ${p.lng.toFixed(5)}) - ${Math.round(dist)} m dari SPPG - ${inside ? 'DALAM AREA' : 'LUAR AREA'}`, inside ? 'bg-emerald-500' : 'bg-red-500');
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
                showAlert.error(msg);
                return null;
            }

            setLoc('Mendeteksi lokasi... aktifkan GPS & tunggu sebentar', 'bg-amber-500');
            try {
                loc = await getLocation(30000, true);
            } catch (err1) {
                if (err1 && err1.code === 1 && state !== 'granted') {
                    const msg = deniedMessage();
                    setLoc(msg, 'bg-red-500');
                    showAlert.error(msg);
                    return null;
                }
                try {
                    loc = await getLocation(20000, false);
                } catch (err) {
                    const msg = locErrorMessage(err);
                    setLoc(msg, 'bg-red-500');
                    showAlert.error(msg);
                    return null;
                }
            }
            setLocStatus(loc);
            return loc;
        }

        async function updateLocation() {
            await ensureLocation();
        }

        function setLoc(text, color) {
            const dot = document.getElementById('loc-dot');
            const txt = document.getElementById('loc-text');
            if (dot) dot.className = 'inline-block w-2.5 h-2.5 rounded-full ' + color;
            if (txt) txt.textContent = text;
        }

        function haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        updateLocation();

        // ===== Tab =====
        function switchTab(tab) {
            document.getElementById('panel-barcode').classList.toggle('hidden', tab !== 'barcode');
            document.getElementById('panel-camera').classList.toggle('hidden', tab !== 'camera');
            document.getElementById('tab-barcode').className = 'tab-btn py-3 rounded-xl font-semibold text-sm ' + (tab === 'barcode' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-600');
            document.getElementById('tab-camera').className = 'tab-btn py-3 rounded-xl font-semibold text-sm ' + (tab === 'camera' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-600');
            if (tab === 'barcode') stopCamera();
            else stopScanner();
        }

        // ===== Scanner barcode =====
        function cameraSupport() {
            if (!navigator.mediaDevices?.getUserMedia) {
                showAlert.error('Halaman ini tidak diakses melalui HTTPS. Kamera & lokasi hanya berfungsi di https:// atau http://localhost. Buka via https atau gunakan fitur di perangkat absen.');
                return false;
            }
            return true;
        }

        function startScanner() {
            if (html5Qr) return;
            if (!cameraSupport()) return;
            const el = document.getElementById('scanner');
            el.innerHTML = '<div id="qr-reader"></div>';
            html5Qr = new Html5Qrcode('qr-reader');
            html5Qr.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decoded) => { onScanned(decoded); },
                () => {}
            ).catch((e) => {
                el.innerHTML = '<div class="scanner-placeholder"><p class="text-sm text-red-500">Tidak dapat mengakses kamera. Periksa izin kamera di browser, pastikan HTTPS, dan coba lagi.</p></div>';
                html5Qr = null;
            });
            document.getElementById('stop-scan-btn').classList.remove('hidden');
        }

        function stopScanner() {
            if (html5Qr) {
                html5Qr.stop().then(() => {
                    html5Qr.clear();
                    html5Qr = null;
                    document.getElementById('scanner').innerHTML = '';
                    document.getElementById('stop-scan-btn').classList.add('hidden');
                }).catch(() => {});
            }
        }

        async function onScanned(code) {
            stopScanner();
            const res = await fetch(`/scan/${encodeURIComponent(code)}`);
            if (res.ok) {
                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const badge = doc.querySelector('[data-employee-badge]');
                if (!badge) { showAlert.error('Barcode tidak valid.'); return; }
                const name = badge.dataset.name;
                const nip = badge.dataset.nip;
                const initial = name.charAt(0).toUpperCase();
                document.getElementById('scan-name').textContent = name;
                document.getElementById('scan-nip').textContent = nip + ' - ' + (badge.dataset.position || '');
                document.getElementById('scan-code').value = code;
                document.getElementById('scan-result').classList.remove('hidden');
                document.getElementById('scan-result').querySelector('.bg-blue-900').textContent = initial;
                await startSelfie('barcode');
            } else {
                showAlert.error('Barcode tidak dikenali sistem.');
            }
        }

        // ===== Selfie saat scan barcode =====
        async function startSelfie(mode) {
            if (!cameraSupport()) return;
            const wrap = document.getElementById(mode === 'barcode' ? 'barcode-selfie-wrap' : '');
            const wantSelfie = document.getElementById('with-selfie-barcode').checked;
            document.getElementById('barcode-selfie-wrap').classList.toggle('hidden', !wantSelfie);
            if (!wantSelfie) return;
            try {
                selfieStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                const video = document.getElementById('selfie-barcode-video');
                video.srcObject = selfieStream;
                await video.play();
            } catch (e) {
                showAlert.error('Gagal akses kamera depan. Periksa izin kamera dan pastikan HTTPS.');
            }
        }

        function captureSelfie(mode) {
            const video = document.getElementById('selfie-barcode-video');
            const canvas = document.getElementById('canvas-barcode');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.7);
            document.getElementById('photo-barcode').value = dataUrl;
            const preview = document.getElementById('preview-barcode');
            preview.src = dataUrl;
            preview.classList.remove('hidden');
            if (selfieStream) { selfieStream.getTracks().forEach(t => t.stop()); selfieStream = null; }
            video.srcObject = null;
            document.getElementById('with-selfie-barcode').disabled = true;
        }

        document.getElementById('with-selfie-barcode')?.addEventListener('change', (e) => {
            document.getElementById('barcode-selfie-wrap').classList.toggle('hidden', !e.target.checked);
            if (e.target.checked) startSelfie('barcode');
        });

        // ===== Kamera absen (mode kamera) =====
        async function startCamera() {
            if (!cameraSupport()) return;
            try {
                camStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                const video = document.getElementById('video-feed');
                video.srcObject = camStream;
                await video.play();
                document.getElementById('cam-start-btn').textContent = 'Kamera aktif';
                document.getElementById('cam-start-btn').disabled = true;
                document.getElementById('cam-capture-btn').disabled = false;
            } catch (e) {
                showAlert.error('Tidak dapat mengakses kamera. Periksa izin kamera di browser dan pastikan HTTPS.');
            }
        }

        function stopCamera() {
            if (camStream) { camStream.getTracks().forEach(t => t.stop()); camStream = null; }
            const video = document.getElementById('video-feed');
            if (video) video.srcObject = null;
            document.getElementById('cam-start-btn').textContent = 'Nyalakan Kamera';
            document.getElementById('cam-start-btn').disabled = false;
            document.getElementById('cam-capture-btn').disabled = true;
        }

        async function captureCameraPhoto() {
            if (!camStream) {
                showAlert.error('Nyalakan kamera terlebih dahulu.');
                return;
            }
            if (!loc) {
                showAlert.error('Menunggu lokasi terdeteksi...');
                const p = await ensureLocation();
                if (!p) return;
            }
            const video = document.getElementById('video-feed');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            document.getElementById('photo-camera').value = canvas.toDataURL('image/jpeg', 0.75);
            document.getElementById('lat-camera').value = loc.lat;
            document.getElementById('lng-camera').value = loc.lng;
            document.getElementById('camera-form').submit();
        }

        // ===== Submit barcode =====
        document.getElementById('barcode-form')?.addEventListener('submit', async (e) => {
            if (!loc) {
                e.preventDefault();
                showAlert.warning('Lokasi belum terdeteksi. Mencoba mendeteksi lokasi, tunggu sebentar...');
                const p = await ensureLocation();
                if (p) {
                    document.getElementById('lat-barcode').value = p.lat;
                    document.getElementById('lng-barcode').value = p.lng;
                    e.target.submit();
                }
                return;
            }
            document.getElementById('lat-barcode').value = loc.lat;
            document.getElementById('lng-barcode').value = loc.lng;
        });

        window.addEventListener('beforeunload', () => {
            if (camStream) camStream.getTracks().forEach(t => t.stop());
            if (selfieStream) selfieStream.getTracks().forEach(t => t.stop());
        });
    </script>
@endpush
