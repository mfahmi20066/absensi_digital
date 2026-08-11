@extends('tata-letak.sidebar')

@section('title', 'Data Absensi - ' . config('app.name'))
@section('page-title', 'Data Absensi')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-500">Tanggal</label>
                <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}" class="mt-1 w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border-gray-300">
                    <option value="all">Semua</option>
                    @foreach (['hadir', 'telat', 'izin', 'sakit', 'cuti', 'alpha'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Area</label>
                <select name="area" class="mt-1 w-full rounded-lg border-gray-300">
                    <option value="all">Semua</option>
                    <option value="dalam" @selected(request('area') === 'dalam')>Dalam Area</option>
                    <option value="luar" @selected(request('area') === 'luar')>Luar Area</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Cari Nama / NIP</label>
                <input type="text" name="search" value="{{ request('search') }}" class="mt-1 w-full rounded-lg border-gray-300">
            </div>
            <button class="px-4 py-2.5 rounded-lg bg-gray-800 text-white text-sm font-semibold">Tampilkan</button>
        </form>
    </div>

    @if (auth()->user()->isAdmin())
        <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
            <h3 class="font-bold text-gray-800 mb-3 text-sm">Absensi Manual (jika karyawan lupa / perangkat bermasalah)</h3>
            <form method="POST" action="{{ route('admin.absensi.store') }}" class="grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
                @csrf
                <div class="col-span-2">
                    <label class="text-xs font-semibold text-gray-500">Karyawan</label>
                    <select name="employee_id" required class="mt-1 w-full rounded-lg border-gray-300">
                        @foreach ($employees as $e)
                            <option value="{{ $e->id }}">{{ $e->user->name }} ({{ $e->nip }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Tanggal</label>
                    <input type="date" name="date" value="{{ $date }}" required class="mt-1 w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Masuk</label>
                    <input type="time" name="time_in" class="mt-1 w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Pulang</label>
                    <input type="time" name="time_out" class="mt-1 w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Status</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-gray-300">
                        @foreach (['hadir', 'telat', 'izin', 'sakit', 'cuti', 'alpha'] as $st)
                            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2 sm:col-span-6">
                    <label class="text-xs font-semibold text-gray-500">Catatan</label>
                    <input type="text" name="notes" class="mt-1 w-full rounded-lg border-gray-300" placeholder="mis. Perangkat karyawan rusak">
                </div>
                <div class="col-span-2 sm:col-span-6">
                    <button class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold">Simpan Absensi Manual</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Masuk</th>
                    <th class="px-4 py-3">Pulang</th>
                    <th class="px-4 py-3">Metode</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Foto</th>
                    <th class="px-4 py-3">Status</th>
                    @if (auth()->user()->isAdmin())
                        <th class="px-4 py-3 text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($attendances as $att)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">{{ $att->employee->user->name }}</div>
                            <div class="text-xs text-gray-400">{{ $att->employee->nip }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $att->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $att->time_in?->format('H:i:s') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $att->time_out?->format('H:i:s') ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs">
                            <div>Masuk: {{ $att->method_in ?? '-' }}</div>
                            <div>Pulang: {{ $att->method_out ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $sppgLat = (float) \App\Models\Pengaturan::get('sppg_latitude', '0');
                                $sppgLon = (float) \App\Models\Pengaturan::get('sppg_longitude', '0');
                                $hasLoc = $att->latitude_in || $att->latitude_out;
                            @endphp
                            @if ($hasLoc)
                                <div class="space-y-1.5">
                                    @if ($att->latitude_in)
                                        @php
                                            $dIn = (int) round(\App\Support\LokasiGeografis::distanceInMeters((float) $att->latitude_in, (float) $att->longitude_in, $sppgLat, $sppgLon));
                                        @endphp
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-[11px] font-semibold text-gray-400">Masuk:</span>
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $att->is_outside_area_in ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                {{ $att->is_outside_area_in ? 'Luar area' : 'Dalam area' }}
                                            </span>
                                            <a href="https://www.google.com/maps?q={{ $att->latitude_in }},{{ $att->longitude_in }}" target="_blank" rel="noopener" title="{{ $att->latitude_in }}, {{ $att->longitude_in }}" class="inline-flex items-center gap-0.5 text-[11px] text-blue-700 hover:underline tabular-nums">
                                                <x-ikon name="map-pin" class="w-3 h-3" /> {{ number_format($dIn) }} m
                                            </a>
                                        </div>
                                    @endif
                                    @if ($att->latitude_out)
                                        @php
                                            $dOut = (int) round(\App\Support\LokasiGeografis::distanceInMeters((float) $att->latitude_out, (float) $att->longitude_out, $sppgLat, $sppgLon));
                                        @endphp
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-[11px] font-semibold text-gray-400">Pulang:</span>
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $att->is_outside_area_out ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                {{ $att->is_outside_area_out ? 'Luar area' : 'Dalam area' }}
                                            </span>
                                            <a href="https://www.google.com/maps?q={{ $att->latitude_out }},{{ $att->longitude_out }}" target="_blank" rel="noopener" title="{{ $att->latitude_out }}, {{ $att->longitude_out }}" class="inline-flex items-center gap-0.5 text-[11px] text-blue-700 hover:underline tabular-nums">
                                                <x-ikon name="map-pin" class="w-3 h-3" /> {{ number_format($dOut) }} m
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($att->photo_in || $att->photo_out)
                                <a href="{{ asset('storage/' . ($att->photo_in ?? $att->photo_out)) }}" target="_blank" class="text-blue-900 hover:underline inline-flex items-center gap-1"><x-ikon name="image" class="w-3.5 h-3.5" /> lihat</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($att->status === 'telat' ? 'bg-amber-100 text-amber-700' : ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700')) }}">
                                {{ $att->statusLabel }}
                            </span>
                            @if ($att->notes)
                                <div class="text-xs text-gray-400 mt-0.5" title="{{ $att->notes }}">{{ Str::limit($att->notes, 30) }}</div>
                            @endif
                        </td>
                        @if (auth()->user()->isAdmin())
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.absensi.edit', $att) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"><x-ikon name="pencil" class="w-4 h-4" /></a>
                                    <form method="POST" action="{{ route('admin.absensi.destroy', $att) }}" onsubmit="return confirmSubmit(this, 'Hapus data absensi ini?', 'Ya, hapus')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-400"><x-ikon name="trash" class="w-4 h-4" /></button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi pada filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $attendances->links() }}</div>
@endsection
