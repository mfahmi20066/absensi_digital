@extends('tata-letak.sidebar')

@section('title', 'Laporan - ' . config('app.name'))
@section('page-title', 'Laporan & Rekap Absensi')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-500">Periode</label>
                <select name="period" class="mt-1 rounded-lg border-gray-300">
                    <option value="daily" @selected(($period ?? 'daily') === 'daily')>Harian</option>
                    <option value="monthly" @selected(($period ?? '') === 'monthly')>Bulanan</option>
                    <option value="yearly" @selected(($period ?? '') === 'yearly')>Tahunan</option>
                </select>
            </div>
            <div id="daily-fields" class="flex gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Tanggal</label>
                    <input type="date" name="date" value="{{ $date ?? today()->toDateString() }}" class="mt-1 rounded-lg border-gray-300">
                </div>
            </div>
            <div id="monthly-fields" class="hidden flex gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Bulan</label>
                    <select name="month" class="mt-1 rounded-lg border-gray-300">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @selected(($month ?? now()->month) == $i)>{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Tahun</label>
                    <select name="year" class="mt-1 rounded-lg border-gray-300">
                        @for ($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" @selected(($year ?? now()->year) == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <button class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold">Tampilkan</button>
            <button name="export" value="xlsx" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold inline-flex items-center gap-1.5"><x-ikon name="download" class="w-4 h-4" /> Export Excel</button>
            <button name="export" value="pdf" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold inline-flex items-center gap-1.5"><x-ikon name="file-text" class="w-4 h-4" /> Export PDF</button>
            <button name="export" value="print" class="px-4 py-2 rounded-lg bg-blue-50 text-blue-900 text-sm font-semibold inline-flex items-center gap-1.5"><x-ikon name="printer" class="w-4 h-4" /> Cetak</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">{{ $title }}</h3>
            <span class="text-sm text-gray-400">{{ $attendances->count() }} catatan</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
            @foreach (['hadir' => 'Hadir', 'telat' => 'Telat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti', 'alpha' => 'Alpha'] as $key => $label)
                <div class="rounded-xl p-3 text-center
                    {{ $key === 'hadir' ? 'bg-emerald-50 text-emerald-700' : ($key === 'telat' ? 'bg-amber-50 text-amber-700' : ($key === 'alpha' ? 'bg-red-50 text-red-700' : 'bg-sky-50 text-sky-700')) }}">
                    <div class="text-2xl font-bold">{{ $summary->get($key, 0) }}</div>
                    <div class="text-xs">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">NIP</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Jabatan</th>
                    <th class="px-4 py-3">Shift</th>
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
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-600">{{ $att->employee->nip }}</td>
                        <td class="px-4 py-2.5 font-semibold text-gray-800">{{ $att->employee->user->name }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $att->employee->position }}</td>
                        <td class="px-4 py-2.5 text-xs">{{ $att->workSchedule?->name ?? '-' }}</td>
                        <td class="px-4 py-2.5">{{ $att->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5">{{ $att->time_in?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-2.5">{{ $att->time_out?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-xs">{{ $att->method_in ?? '-' }} / {{ $att->method_out ?? '-' }}</td>
                        <td class="px-4 py-2.5">
                            @if ($att->latitude_in)
                                <span class="text-xs {{ ($att->is_outside_area_in || $att->is_outside_area_out) ? 'text-red-600 font-semibold' : 'text-emerald-600 font-semibold' }}">
                                    {{ ($att->is_outside_area_in || $att->is_outside_area_out) ? 'Luar' : 'Dalam' }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($att->status === 'telat' ? 'bg-amber-100 text-amber-700' : ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700')) }}">
                                {{ $att->statusLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">Tidak ada data pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        const period = document.querySelector('select[name="period"]');
        function togglePeriodFields() {
            const daily = document.getElementById('daily-fields');
            const monthly = document.getElementById('monthly-fields');
            const isMonthly = period.value === 'monthly';
            const isYearly = period.value === 'yearly';
            daily.classList.toggle('hidden', isMonthly || isYearly);
            monthly.classList.toggle('hidden', !(isMonthly || isYearly));
            if (isYearly) monthly.querySelector('select[name="month"]').disabled = true;
            else monthly.querySelector('select[name="month"]').disabled = false;
        }
        period.addEventListener('change', togglePeriodFields);
        togglePeriodFields();
    </script>
@endpush
