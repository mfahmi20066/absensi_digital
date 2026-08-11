@extends('tata-letak.sidebar')

@section('title', 'Dashboard - ' . config('app.name'))
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="welcome-card bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-800">Selamat datang, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-gray-500 mt-1">{{ today()->translatedFormat('l, d F Y') }}</p>
        </div>

        @if ($todayAbsensi)
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 {{ $todayAbsensi->time_out ? 'border-blue-900' : 'border-amber-400' }}">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-gray-800">Absensi Hari Ini</h3>
                        <div class="text-sm text-gray-500 mt-1">
                            Status: <span class="font-semibold {{ $todayAbsensi->status === 'telat' ? 'text-amber-600' : 'text-emerald-600' }}">{{ strtoupper($todayAbsensi->statusLabel) }}</span>
                            @if ($todayAbsensi->is_outside_area_in)
                                <span class="ml-2 inline-block px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Di Luar Area</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-blue-50 rounded-lg px-4 py-3">
                            <div class="text-xs text-gray-500">Masuk</div>
                            <div class="text-lg font-bold text-blue-900">{{ $todayAbsensi->time_in?->format('H:i:s') ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $todayAbsensi->method_in }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <div class="text-xs text-gray-500">Pulang</div>
                            <div class="text-lg font-bold {{ $todayAbsensi->time_out ? 'text-gray-800' : 'text-gray-300' }}">{{ $todayAbsensi->time_out?->format('H:i:s') ?? 'Belum' }}</div>
                            <div class="text-xs text-gray-400">{{ $todayAbsensi->method_out ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Hadir</div>
                <div class="text-3xl font-bold text-emerald-600 mt-1">{{ $monthly->get('hadir', 0) + $monthly->get('telat', 0) }}</div>
                <div class="text-xs text-gray-500 mt-1">bulan ini (termasuk telat)</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Telat</div>
                <div class="text-3xl font-bold text-amber-500 mt-1">{{ $monthly->get('telat', 0) }}</div>
                <div class="text-xs text-gray-500 mt-1">bulan ini</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Izin / Sakit / Cuti</div>
                <div class="text-3xl font-bold text-sky-500 mt-1">{{ $monthly->get('izin', 0) + $monthly->get('sakit', 0) + $monthly->get('cuti', 0) }}</div>
                <div class="text-xs text-gray-500 mt-1">bulan ini</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Alpha</div>
                <div class="text-3xl font-bold text-red-500 mt-1">{{ $monthly->get('alpha', 0) }}</div>
                <div class="text-xs text-gray-500 mt-1">bulan ini</div>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-violet-500">
                <div class="text-xs text-gray-500 uppercase font-semibold">Sisa Cuti Tahunan</div>
                <div class="text-3xl font-bold text-violet-600 mt-1">{{ $sisaCuti }} <span class="text-sm text-gray-400">hari</span></div>
                <div class="text-xs text-gray-500 mt-1">jatah tahun {{ now()->year }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Lembur Disetujui</div>
                <div class="text-3xl font-bold text-blue-900 mt-1">{{ $pendingOvertime === 0 ? '0' : $pendingOvertime }}</div>
                <div class="text-xs text-gray-500 mt-1">pengajuan menunggu</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Koreksi Absensi</div>
                <div class="text-3xl font-bold text-sky-600 mt-1">{{ $pendingCorrections }}</div>
                <div class="text-xs text-gray-500 mt-1">pengajuan menunggu</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Pengajuan Lainnya</div>
                <div class="text-3xl font-bold text-amber-500 mt-1">{{ $pendingLeaves }}</div>
                <div class="text-xs text-gray-500 mt-1">izin/sakit/cuti pending</div>
            </div>
        </div>

        @if ($pendingLeaves > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-800">
                Anda memiliki <b>{{ $pendingLeaves }}</b> pengajuan yang menunggu persetujuan.
                <a href="{{ route('karyawan.cuti.index') }}" class="font-semibold underline">Lihat status</a>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">Absensi Terakhir Saya</h3>
                    <a href="{{ route('karyawan.riwayat') }}" class="text-sm text-blue-900 font-semibold hover:underline">Lihat semua &rarr;</a>
                </div>
                @if ($recent->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada riwayat absensi.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recent as $att)
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2 last:border-0">
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">{{ $att->date->translatedFormat('l, d F Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $att->time_in?->format('H:i') ?? '-' }} - {{ $att->time_out?->format('H:i') ?? '-' }} &middot; {{ $att->method_in ?? '-' }}</div>
                                </div>
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($att->status === 'telat' ? 'bg-amber-100 text-amber-700' : ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700')) }}">
                                    {{ $att->statusLabel }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">Aksi Cepat</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('karyawan.absen.index') }}" class="bg-blue-900 hover:bg-blue-950 text-white rounded-xl p-4 text-center">
                        <x-ikon name="scan" class="w-8 h-8 mx-auto mb-2" />
                        <div class="font-semibold text-sm">Absen Masuk / Pulang</div>
                    </a>
                    <a href="{{ route('karyawan.absen.qr') }}" class="bg-white border border-blue-300 hover:bg-blue-50 text-blue-900 rounded-xl p-4 text-center">
                        <x-ikon name="qrcode" class="w-8 h-8 mx-auto mb-2" />
                        <div class="font-semibold text-sm">Tampilkan Barcode</div>
                    </a>
                    <a href="{{ route('karyawan.cuti.index') }}" class="bg-white border border-sky-300 hover:bg-sky-50 text-sky-800 rounded-xl p-4 text-center">
                        <x-ikon name="clipboard" class="w-8 h-8 mx-auto mb-2" />
                        <div class="font-semibold text-sm">Ajukan Izin / Sakit</div>
                    </a>
                    <a href="{{ route('karyawan.lembur.index') }}" class="bg-white border border-violet-300 hover:bg-violet-50 text-violet-800 rounded-xl p-4 text-center">
                        <x-ikon name="clock" class="w-8 h-8 mx-auto mb-2" />
                        <div class="font-semibold text-sm">Ajukan Lembur</div>
                    </a>
                    <a href="{{ route('karyawan.koreksi.index') }}" class="bg-white border border-teal-300 hover:bg-teal-50 text-teal-800 rounded-xl p-4 text-center">
                        <x-ikon name="pencil" class="w-8 h-8 mx-auto mb-2" />
                        <div class="font-semibold text-sm">Koreksi Absensi</div>
                    </a>
                    <a href="{{ route('karyawan.rekap') }}" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl p-4 text-center">
                        <x-ikon name="chart" class="w-8 h-8 mx-auto mb-2" />
                        <div class="font-semibold text-sm">Rekap Bulanan</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
