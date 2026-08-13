<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/logos/favicon.png?v=3') }}">

        <title>@yield('title', config('app.name'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('light');
            }
        </script>
        @stack('styles')
    </head>
    <body class="panel-auth font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[300px] -translate-x-full transition-transform duration-300 ease-in-out flex flex-col bg-slate-950 text-white shrink-0 overflow-y-auto lg:static lg:translate-x-0 lg:w-64 lg:h-screen lg:sticky lg:top-0 lg:overflow-hidden">
                <div class="px-5 pr-14 lg:pr-5 py-5 border-b border-slate-900 flex items-center gap-3 relative">
                    <img src="{{ asset('images/logos/sppg-logo-white.png') }}" alt="Logo" class="sidebar-logo-dark w-11 h-11 rounded-lg object-contain shrink-0">
                    <img src="{{ asset('images/logos/sppg-logo.png') }}" alt="Logo" class="sidebar-logo-light w-11 h-11 rounded-lg object-contain shrink-0">
                    <div class="min-w-0">
                        <div class="font-bold leading-snug text-sm lg:text-base">{{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</div>
                        <div class="text-xs text-blue-300">Sistem Absensi Digital</div>
                    </div>
                    <button id="sidebar-close" type="button" aria-label="Tutup menu" class="lg:hidden absolute top-2.5 right-2.5 p-2 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @include('tata-letak.tautan-nav')

                <div class="mt-auto px-3 py-4 border-t border-slate-900 space-y-1">
                    <button type="button" data-theme-toggle title="Ganti mode terang/gelap" aria-label="Ganti mode terang/gelap" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white text-sm transition-colors">
                        <svg class="theme-icon-sun w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg class="theme-icon-moon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="theme-label">Mode Terang</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirmSubmit(this, 'Anda akan keluar dari sistem. Pastikan pekerjaan Anda sudah tersimpan.', 'Ya, keluar')">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-300 hover:bg-white/10 hover:text-white text-sm">
                            <x-ikon name="logout" class="w-5 h-5" />
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                <header class="bg-white shadow-sm sticky top-0 z-30">
                    <div class="px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <button id="sidebar-toggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100" aria-label="Menu">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <div class="lg:hidden flex items-center gap-2">
                                <img src="{{ asset('images/logos/sppg-logo.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                                <span class="font-bold text-blue-950 text-sm">SPPG Palopo</span>
                            </div>
                            <h1 class="hidden lg:block text-lg font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="relative" id="user-menu">
                            <button id="user-menu-btn" class="flex items-center gap-2 sm:gap-3 rounded-xl p-1.5 hover:bg-gray-100 transition" aria-label="Menu pengguna">
                                <div class="text-right hidden sm:block">
                                    <div class="text-sm font-semibold text-gray-800 leading-tight">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role?->label ?? '') }}</div>
                                </div>
                                <div class="w-9 h-9 rounded-full bg-blue-900 text-white flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <svg class="w-4 h-4 text-gray-400 hidden sm:block transition-transform duration-200" id="user-menu-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="user-menu-dropdown" class="profile-menu hidden absolute right-0 mt-2 w-60 z-50">
                                <div class="pm-head">
                                    <div class="pm-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                                    <div class="min-w-0">
                                        <div class="pm-name truncate">{{ auth()->user()->name }}</div>
                                        <div class="pm-email truncate">{{ auth()->user()->email }}</div>
                                    </div>
                                </div>
                                <a href="{{ route('profil.edit') }}" class="pm-item">
                                    <x-ikon name="user" class="w-4 h-4 pm-icon" /> Profil
                                </a>
                                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirmSubmit(this, 'Anda akan keluar dari sistem. Pastikan pekerjaan Anda sudah tersimpan.', 'Ya, keluar')">
                                    @csrf
                                    <button type="submit" class="pm-item pm-danger w-full">
                                        <x-ikon name="logout" class="w-4 h-4 pm-icon" /> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 w-full">
                    @yield('content')
                </main>

                <footer class="px-6 py-3 text-center text-xs text-gray-400 border-t bg-white">
                    &copy; {{ date('Y') }} {{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }} - Sistem Absensi Digital
                </footer>
            </div>
        </div>

        <div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden"></div>

        @php
            $appAlerts = ['type' => null, 'message' => null, 'errors' => []];
            if (session('success')) {
                $appAlerts = ['type' => 'success', 'message' => session('success')];
            } elseif (session('error')) {
                $appAlerts = ['type' => 'error', 'message' => session('error')];
            }
            if ($errors->any()) {
                $appAlerts = ['type' => 'validation', 'title' => 'Validasi Gagal', 'errors' => $errors->all()];
            }
        @endphp
        <script type="application/json" id="app-alerts">@json($appAlerts)</script>

        <script>
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const toggle = document.getElementById('sidebar-toggle');
            const closeBtn = document.getElementById('sidebar-close');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                document.body.classList.remove('overflow-hidden');
            }

            if (toggle) toggle.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (overlay) overlay.addEventListener('click', closeSidebar);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeSidebar();
            });

            const userMenuBtn = document.getElementById('user-menu-btn');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            const userMenuChevron = document.getElementById('user-menu-chevron');
            if (userMenuBtn) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isHidden = userMenuDropdown.classList.toggle('hidden');
                    if (userMenuChevron) userMenuChevron.classList.toggle('rotate-180', !isHidden);
                });
                document.addEventListener('click', (e) => {
                    if (!document.getElementById('user-menu').contains(e.target)) {
                        userMenuDropdown.classList.add('hidden');
                        if (userMenuChevron) userMenuChevron.classList.remove('rotate-180');
                    }
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        userMenuDropdown.classList.add('hidden');
                        if (userMenuChevron) userMenuChevron.classList.remove('rotate-180');
                    }
                });
            }
        </script>
        @stack('scripts')
    </body>
</html>
