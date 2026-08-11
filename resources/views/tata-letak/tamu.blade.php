@props(['title' => null, 'subtitle' => null])

@php
    $namaSppg = \App\Models\Pengaturan::get('sppg_name', 'SPPG');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logos/favicon.png') }}">

        <title>{{ $title ? $title . ' - ' . $namaSppg : $namaSppg }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('light');
            }
        </script>
    </head>
    <body class="guest-page font-sans antialiased text-white bg-[#0b1220] min-h-screen">
        <button type="button" data-theme-toggle title="Ganti mode terang/gelap" aria-label="Ganti mode terang/gelap" class="theme-toggle fixed top-4 right-4 z-50 w-10 h-10 rounded-full flex items-center justify-center border border-white/20 bg-white/10 text-blue-100 hover:bg-white/20 transition-colors duration-300">
            <svg class="theme-icon-sun w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <svg class="theme-icon-moon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
        </button>
        <div class="min-h-screen lg:grid lg:grid-cols-2">

            {{-- Panel Brand --}}
            <div class="hidden lg:flex flex-col justify-between p-12 relative overflow-hidden">
                {{-- Motif: lingkaran konsentris + glow halus senada #0b1220 --}}
                <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                    <div class="absolute -top-40 -right-32 w-[520px] h-[520px] rounded-full border border-white/5"></div>
                    <div class="absolute -top-24 -right-16 w-[360px] h-[360px] rounded-full border border-white/10"></div>
                    <div class="absolute top-16 -right-4 w-40 h-40 rounded-full border border-blue-400/20"></div>
                    <div class="absolute bottom-24 -left-48 w-[460px] h-[460px] rounded-full border border-white/5"></div>
                    <div class="absolute -bottom-24 -left-24 w-[300px] h-[300px] rounded-full border border-white/10"></div>
                    <div class="absolute bottom-0 -left-32 w-[420px] h-[420px] bg-blue-500/10 blur-3xl"></div>
                    <div class="absolute top-1/3 right-0 w-64 h-64 bg-blue-400/5 blur-3xl"></div>
                </div>

                <div class="relative flex items-center gap-4">
                    <img src="{{ asset('images/logos/sppg-logo-white.png') }}" alt="Logo SPPG" class="w-14 h-14 rounded-xl object-contain">
                    <div>
                        <div class="text-white text-xl font-bold leading-tight">{{ $namaSppg }}</div>
                        <div class="text-blue-300 text-sm">Sistem Absensi Digital</div>
                    </div>
                </div>

                <div class="relative space-y-6">
                    <h1 class="text-white text-3xl font-bold leading-snug">
                        Absensi Digital yang<br>Mudah &amp; Terpercaya
                    </h1>
                    <ul class="space-y-3.5 text-blue-100 text-sm">
                        <li class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center">
                                <x-ikon name="scan" class="w-4 h-4 text-blue-300" />
                            </span>
                            Scan QR &amp; foto absensi masuk-pulang setiap hari
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center">
                                <x-ikon name="clipboard" class="w-4 h-4 text-blue-300" />
                            </span>
                            Izin, sakit, cuti, lembur &amp; koreksi dalam satu aplikasi
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center">
                                <x-ikon name="chart" class="w-4 h-4 text-blue-300" />
                            </span>
                            Laporan &amp; rekap otomatis untuk admin dan manajer
                        </li>
                    </ul>
                </div>

                <div class="relative text-blue-400 text-xs">
                    &copy; {{ now()->year }} {{ $namaSppg }}
                </div>
            </div>

            {{-- Panel Form --}}
            <div class="flex min-h-screen items-center justify-center px-4 sm:px-6 py-10">
                <div class="w-full max-w-md">
                    <div class="lg:hidden flex flex-col items-center mb-8">
                        <img src="{{ asset('images/logos/sppg-logo-white.png') }}" alt="Logo SPPG" class="w-16 h-16 rounded-2xl object-contain">
                        <div class="mt-2 text-lg font-bold text-white">{{ $namaSppg }}</div>
                        <div class="text-xs text-blue-300">Sistem Absensi Digital</div>
                    </div>

                    <div class="bg-white/10 border border-white/10 rounded-2xl backdrop-blur p-8">
                        @if ($title)
                            <div class="mb-6">
                                <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
                                @if ($subtitle)
                                    <p class="mt-1.5 text-sm text-blue-200 leading-relaxed">{{ $subtitle }}</p>
                                @endif
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
