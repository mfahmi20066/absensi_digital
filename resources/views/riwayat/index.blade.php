@extends('tata-letak.sidebar')

@section('title', 'Riwayat Absensi - ' . config('app.name'))
@section('page-title', 'Riwayat Absensi')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1">
                <label class="text-xs font-semibold text-gray-500">Bulan</label>
                <select name="month" class="mt-1 w-full rounded-lg border-gray-300">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" @selected(request('month', now()->month) == $i)>{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="flex-1">
                <label class="text-xs font-semibold text-gray-500">Tahun</label>
                <select name="year" class="mt-1 w-full rounded-lg border-gray-300">
                    @for ($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" @selected(request('year', now()->year) == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button class="px-5 py-2.5 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Masuk</th>
                    <th class="px-4 py-3">Pulang</th>
                    <th class="px-4 py-3">Metode</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($attendances as $att)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $att->date->translatedFormat('d M Y') }} <span class="text-gray-400 font-normal">{{ $att->date->translatedFormat('D') }}</span></td>
                        <td class="px-4 py-3">{{ $att->time_in?->format('H:i:s') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $att->time_out?->format('H:i:s') ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs">
                            @if ($att->photo_in)
                                <a href="{{ asset('storage/' . $att->photo_in) }}" target="_blank" class="text-blue-900 hover:underline inline-flex items-center gap-1"><x-ikon name="image" class="w-3.5 h-3.5" /> {{ $att->method_in ?? 'foto' }}</a>
                            @else
                                {{ $att->method_in ?? '-' }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($att->latitude_in)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $att->is_outside_area_in ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $att->is_outside_area_in ? 'Luar area' : 'Dalam area' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($att->status === 'telat' ? 'bg-amber-100 text-amber-700' : ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700')) }}">
                                {{ $att->statusLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada data absensi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attendances->links() }}</div>
@endsection
