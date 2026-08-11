@extends('tata-letak.sidebar')

@section('title', 'Dashboard - ' . config('app.name'))
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="welcome-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Selamat datang, {{ auth()->user()->name }}!</h2>
                    <p class="text-sm text-gray-500">Rekap absensi hari ini: <span class="font-semibold text-gray-700">{{ $today->translatedFormat('l, d F Y') }}</span></p>
                </div>
                <div class="text-sm bg-blue-50 text-blue-900 px-4 py-2 rounded-lg">
                    {{ \App\Models\Pengaturan::get('sppg_name') }} - Radius absen {{ \App\Models\Pengaturan::get('radius_meter') }} m
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total Karyawan</div>
                <div class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_employees'] }}</div>
                <div class="text-xs text-blue-900 mt-1">Terdaftar di sistem</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Absen Masuk Hari Ini</div>
                <div class="text-3xl font-bold text-emerald-600 mt-1">{{ $stats['hadir'] }}</div>
                <div class="text-xs text-gray-500 mt-1">dari {{ $stats['total_employees'] }} karyawan</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Telat Hari Ini</div>
                <div class="text-3xl font-bold text-amber-500 mt-1">{{ $stats['telat'] }}</div>
                <div class="text-xs text-gray-500 mt-1">karyawan telat</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Belum Absen</div>
                <div class="text-3xl font-bold text-red-500 mt-1">{{ $stats['belum_absen'] }}</div>
                <div class="text-xs text-gray-500 mt-1">belum absen masuk</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Pengajuan Menunggu</h3>
                <div class="text-4xl font-bold {{ $stats['pending_leave'] > 0 ? 'text-amber-500' : 'text-gray-300' }}">{{ $stats['pending_leave'] }}</div>
                <div class="text-sm text-gray-500 mt-1">izin / sakit / cuti pending</div>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.pengajuan-cuti.index') : route('manajer.pengajuan-cuti.index') }}" class="inline-block mt-4 text-sm text-blue-900 font-semibold hover:underline">Kelola pengajuan &rarr;</a>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 lg:col-span-2">
                <h3 class="font-bold text-gray-800 mb-4">Status Kehadiran Hari Ini</h3>
                @php
                    $hadir = $stats['hadir'];
                    $sakit = $stats['sakit'];
                    $izin = $stats['izin'];
                    $cuti = $stats['cuti'];
                    $belum = $stats['belum_absen'];
                    $total = max(1, $stats['total_employees']);
                    $hadirPct = round($hadir / $total * 100);
                    $sakitPct = round($sakit / $total * 100);
                    $izinPct = round($izin / $total * 100);
                    $cutiPct = round($cuti / $total * 100);
                    $belumPct = max(0, 100 - $hadirPct - $sakitPct - $izinPct - $cutiPct);
                @endphp
                <div class="flex h-5 rounded-full overflow-hidden mb-4">
                    <div class="bg-emerald-500" style="width: {{ $hadirPct }}%"></div>
                    <div class="bg-amber-400" style="width: {{ $sakitPct }}%"></div>
                    <div class="bg-sky-400" style="width: {{ $izinPct }}%"></div>
                    <div class="bg-violet-400" style="width: {{ $cutiPct }}%"></div>
                    <div class="bg-gray-200" style="width: {{ $belumPct }}%"></div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-sm">
                    <div><span class="inline-block w-3 h-3 rounded-full bg-emerald-500 mr-1"></span>Hadir: <b>{{ $hadir }}</b></div>
                    <div><span class="inline-block w-3 h-3 rounded-full bg-amber-400 mr-1"></span>Sakit: <b>{{ $sakit }}</b></div>
                    <div><span class="inline-block w-3 h-3 rounded-full bg-sky-400 mr-1"></span>Izin: <b>{{ $izin }}</b></div>
                    <div><span class="inline-block w-3 h-3 rounded-full bg-violet-400 mr-1"></span>Cuti: <b>{{ $cuti }}</b></div>
                    <div><span class="inline-block w-3 h-3 rounded-full bg-gray-200 mr-1"></span>Belum: <b>{{ $belum }}</b></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Kehadiran 6 Bulan Terakhir</h3>
                @php
                    $labels = collect();
                    for ($i = 5; $i >= 0; $i--) {
                        $labels->push(now()->subMonths($i)->format('Y-m'));
                    }
                    $max = max(1, $monthly->max() ?? 1);
                @endphp
                <div class="flex items-end gap-2 h-40">
                    @foreach ($labels as $bulan)
                        @php
                            $count = $monthly->get($bulan) ?? 0;
                            $h = round($count / $max * 100);
                            $h = max(4, $h);
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="text-xs font-semibold text-gray-600">{{ $count }}</div>
                            <div class="w-full rounded-t-lg bg-emerald-500" style="height: {{ $h }}px"></div>
                            <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('M') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Absensi Terakhir Hari Ini</h3>
                @if ($recentAbsensis->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada absensi hari ini.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recentAbsensis as $att)
                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-2 last:border-0">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-xs shrink-0">{{ strtoupper(substr($att->employee->user->name, 0, 1)) }}</div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-800 truncate">{{ $att->employee->user->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $att->employee->nip }} &middot; {{ $att->employee->position }}</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $att->status === 'telat' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $att->status === 'telat' ? 'Telat' : 'Hadir' }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $att->time_in?->format('H:i') ?? '-' }} <span class="text-gray-300">&middot;</span> {{ $att->method_in }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
