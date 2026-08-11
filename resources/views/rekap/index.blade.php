@extends('tata-letak.sidebar')

@section('title', 'Rekap Bulanan - ' . config('app.name'))
@section('page-title', 'Rekap Bulanan')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1">
                <label class="text-xs font-semibold text-gray-500">Bulan</label>
                <select name="month" class="mt-1 w-full rounded-lg border-gray-300">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" @selected($month == $i)>{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex-1">
                <label class="text-xs font-semibold text-gray-500">Tahun</label>
                <select name="year" class="mt-1 w-full rounded-lg border-gray-300">
                    @for ($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button class="px-5 py-2.5 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold">Tampilkan</button>
        </form>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-xs text-gray-500 uppercase font-semibold">Hadir</div>
            <div class="text-3xl font-bold text-emerald-600 mt-1">{{ $summary['hadir'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-xs text-gray-500 uppercase font-semibold">Telat</div>
            <div class="text-3xl font-bold text-amber-500 mt-1">{{ $summary['telat'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-xs text-gray-500 uppercase font-semibold">Izin / Sakit / Cuti</div>
            <div class="text-3xl font-bold text-sky-500 mt-1">{{ $summary['izin'] + $summary['sakit'] + $summary['cuti'] }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 text-center">
            <div class="text-xs text-gray-500 uppercase font-semibold">Alpha</div>
            <div class="text-3xl font-bold text-red-500 mt-1">{{ $summary['alpha'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-5">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="text-xs text-gray-500 uppercase font-semibold">Hari Kerja Efektif</div>
            <div class="text-2xl font-bold text-gray-800 mt-1">{{ $summary['workdays'] }} hari</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="text-xs text-gray-500 uppercase font-semibold">Total Jam Kerja</div>
            <div class="text-2xl font-bold text-gray-800 mt-1">{{ $summary['working_hours'] }} jam</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Detail Harian - {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</h3>
            <button onclick="window.print()" class="text-sm text-blue-900 font-semibold hover:underline inline-flex items-center gap-1.5"><x-ikon name="printer" class="w-4 h-4" /> Cetak</button>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Masuk</th>
                    <th class="px-4 py-3">Pulang</th>
                    <th class="px-4 py-3">Durasi</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($records as $att)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $att->date->translatedFormat('d M Y') }} <span class="text-gray-400 font-normal">{{ $att->date->translatedFormat('D') }}</span></td>
                        <td class="px-4 py-3">{{ $att->time_in?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $att->time_out?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $att->time_in && $att->time_out ? $att->time_out->diffInHours($att->time_in) . ' jam ' . $att->time_out->diffInMinutes($att->time_in) % 60 . ' mnt' : '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($att->status === 'telat' ? 'bg-amber-100 text-amber-700' : ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700')) }}">
                                {{ $att->statusLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada data pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
