<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('images/logos/favicon.png?v=3') }}">
        <title>{{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }} - Absensi Digital</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('light');
            }
        </script>
    </head>
    <body class="landing font-sans antialiased bg-[#0b1220] min-h-screen flex flex-col transition-colors duration-300">
        <header class="px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logos/sppg-logo-white.png') }}" alt="Logo SPPG" class="w-12 h-12 rounded-xl object-contain">
                <div>
                    <div class="text-white font-bold">{{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</div>
                    <div class="text-blue-300 text-xs">Sistem Absensi Digital</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" data-theme-toggle title="Ganti mode terang/gelap" aria-label="Ganti mode terang/gelap" class="theme-toggle w-10 h-10 rounded-full flex items-center justify-center border border-white/20 bg-white/10 text-blue-100 hover:bg-white/20 transition-colors duration-300">
                    <svg class="theme-icon-sun w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <svg class="theme-icon-moon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>
                @auth
                    <a href="{{ route('dasbor') }}" class="px-4 py-2 rounded-lg bg-white text-blue-950 font-semibold text-sm hover:bg-blue-50">Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg border border-blue-400 text-blue-100 font-semibold text-sm hover:bg-white/10">Daftar</a>
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-white text-blue-950 font-semibold text-sm hover:bg-blue-50">Masuk</a>
                @endauth
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-6 py-10">
            <div class="max-w-3xl w-full text-center space-y-8">
                <img src="{{ asset('images/logos/sppg-logo-white.png') }}" alt="Logo" class="w-28 h-28 mx-auto rounded-3xl shadow-2xl object-contain">
                <div class="space-y-3">
                    <h1 class="text-3xl sm:text-4xl font-bold text-white">{{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</h1>
                    <p class="text-blue-200">{{ \App\Models\Pengaturan::get('sppg_address', '') }}</p>
                    <p class="text-blue-300 text-sm">Sistem absensi digital berbasis barcode & foto kamera dengan pelacakan lokasi.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
                    <div class="feature-card bg-white/10 rounded-xl p-5 text-white">
                        <x-ikon name="scan" class="w-8 h-8 mb-2 text-blue-300" />
                        <div class="font-semibold mb-1">Scan Barcode</div>
                        <div class="text-sm text-blue-200">Absen masuk/pulang cukup scan barcode unik milik Anda.</div>
                    </div>
                    <div class="feature-card bg-white/10 rounded-xl p-5 text-white">
                        <x-ikon name="camera" class="w-8 h-8 mb-2 text-blue-300" />
                        <div class="font-semibold mb-1">Foto Kamera</div>
                        <div class="text-sm text-blue-200">Foto wajah otomatis diambil sebagai bukti kehadiran.</div>
                    </div>
                    <div class="feature-card bg-white/10 rounded-xl p-5 text-white">
                        <x-ikon name="map-pin" class="w-8 h-8 mb-2 text-blue-300" />
                        <div class="font-semibold mb-1">Pelacakan Lokasi</div>
                        <div class="text-sm text-blue-200">Lokasi absen dicatat & dibandingkan dengan area SPPG.</div>
                    </div>
                </div>
                @auth
                    <a href="{{ route('dasbor') }}" class="inline-block px-6 py-3 rounded-xl bg-white text-blue-950 font-bold hover:bg-blue-50">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-block px-6 py-3 rounded-xl bg-white text-blue-950 font-bold hover:bg-blue-50">Mulai Absen - Masuk ke Akun</a>
                @endauth
            </div>
        </main>

        <footer class="px-6 py-4 text-center text-xs text-blue-400">
            &copy; {{ date('Y') }} {{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}
        </footer>
    </body>
</html>
