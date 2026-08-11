@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $isManajer = $user->isManajer();
    $isKaryawan = $user->isKaryawan();
    $active = request()->route()?->getName() ?? '';
@endphp

<nav class="sidebar-nav flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
    <a href="{{ route('dasbor') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'dasbor' ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>

    @if ($isKaryawan)
        <div class="pt-3 pb-1 text-xs uppercase tracking-wider text-blue-400 font-semibold">Absensi</div>
        <a href="{{ route('karyawan.absen.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'karyawan.absen') && $active !== 'karyawan.absen.qr' ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Absen Masuk / Pulang
        </a>
        <a href="{{ route('karyawan.absen.qr') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $active === 'karyawan.absen.qr' ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M18 10h2M7 12v4m-3 0h6v4H4z"/></svg>
            Barcode Saya
        </a>
        <a href="{{ route('karyawan.riwayat') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'karyawan.riwayat') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Riwayat Absensi
        </a>
        <a href="{{ route('karyawan.rekap') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'karyawan.rekap') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Rekap Bulanan
        </a>
        <a href="{{ route('karyawan.cuti.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'karyawan.cuti') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Izin / Sakit / Cuti
        </a>
        <a href="{{ route('karyawan.lembur.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'karyawan.lembur') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Lembur
        </a>
        <a href="{{ route('karyawan.koreksi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'karyawan.koreksi') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Koreksi Absensi
        </a>
    @endif

    @if ($isAdmin || $isManajer)
        <div class="pt-3 pb-1 text-xs uppercase tracking-wider text-blue-400 font-semibold">Kepegawaian</div>
        @if ($isAdmin)
            <a href="{{ route('admin.karyawan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.karyawan') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Data Karyawan
            </a>
            <a href="{{ route('admin.jadwal-kerja.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.jadwal-kerja') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Jadwal Kerja
            </a>
            <a href="{{ route('admin.jabatan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.jabatan') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Jabatan
            </a>
            <a href="{{ route('admin.barcode.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.barcode') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5v14m4-14v14m4-14v14m4-14v14m4-14v14"/></svg>
                Barcode / QR
            </a>
        @else
            <a href="{{ route('manajer.karyawan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'manajer.karyawan') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Data Karyawan
            </a>
        @endif

        <div class="pt-3 pb-1 text-xs uppercase tracking-wider text-blue-400 font-semibold">Absensi</div>
        <a href="{{ $isAdmin ? route('admin.absensi.index') : route('manajer.absensi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.absensi') || str_starts_with($active, 'manajer.absensi') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Data Absensi
        </a>
        <a href="{{ $isAdmin ? route('admin.pengajuan-cuti.index') : route('manajer.pengajuan-cuti.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.pengajuan-cuti') || str_starts_with($active, 'manajer.pengajuan-cuti') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Pengajuan Izin
        </a>
        <a href="{{ $isAdmin ? route('admin.lembur.index') : route('manajer.lembur.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.lembur') || str_starts_with($active, 'manajer.lembur') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Lembur
        </a>
        <a href="{{ $isAdmin ? route('admin.koreksi.index') : route('manajer.koreksi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.koreksi') || str_starts_with($active, 'manajer.koreksi') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Koreksi Absensi
        </a>
        <a href="{{ $isAdmin ? route('admin.laporan.index') : route('manajer.laporan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.laporan') || str_starts_with($active, 'manajer.laporan') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Laporan
        </a>
    @endif

    @if ($isAdmin)
        <div class="pt-3 pb-1 text-xs uppercase tracking-wider text-blue-400 font-semibold">Sistem</div>
        <a href="{{ route('admin.pengaturan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.pengaturan') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Pengaturan
        </a>
        <a href="{{ route('admin.pengguna.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.pengguna') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Kelola Pengguna
        </a>
        <a href="{{ route('admin.log-audit.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ str_starts_with($active, 'admin.log-audit') ? 'bg-blue-900 text-white' : 'text-slate-300 hover:bg-white/10' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Log Aktivitas
        </a>
    @endif
</nav>
