@extends('tata-letak.sidebar')

@section('title', 'Data Karyawan - ' . config('app.name'))
@section('page-title', 'Data Karyawan')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIP / jabatan..." class="flex-1 rounded-lg border-gray-300">
                <select name="status" class="rounded-lg border-gray-300">
                    <option value="all">Semua Status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                    <option value="resign" @selected(request('status') === 'resign')>Resign</option>
                </select>
                <button class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold">Cari</button>
            </form>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.karyawan.create') }}" class="px-4 py-2 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold text-center whitespace-nowrap inline-flex items-center gap-1.5"><x-ikon name="plus" class="w-4 h-4" /> Tambah Karyawan</a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">NIP</th>
                    <th class="px-4 py-3">Jabatan</th>
                    <th class="px-4 py-3">Shift</th>
                    <th class="px-4 py-3">Akun</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($employees as $emp)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-sm">{{ strtoupper(substr($emp->user?->name ?? '-', 0, 1)) }}</div>
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $emp->user?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $emp->phone ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $emp->nip }}</td>
                        <td class="px-4 py-3">{{ $emp->position }}</td>
                        <td class="px-4 py-3 text-xs">{{ $emp->workSchedule?->name ?? '-' }}<br><span class="text-gray-400">{{ $emp->workSchedule?->time_in?->format('H:i') }} - {{ $emp->workSchedule?->time_out?->format('H:i') }}</span></td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $emp->user?->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $emp->user?->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $emp->user?->role?->label }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $emp->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : ($emp->status === 'nonaktif' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($emp->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('admin.barcode.print', $emp) }}" title="Cetak barcode" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"><x-ikon name="qrcode" class="w-4 h-4" /></a>
                                    <a href="{{ route('admin.karyawan.edit', $emp) }}" title="Edit" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"><x-ikon name="pencil" class="w-4 h-4" /></a>
                                    <form method="POST" action="{{ route('admin.karyawan.destroy', $emp) }}" onsubmit="return confirmSubmit(this, 'Hapus karyawan {{ $emp->user?->name ?? '-' }} beserta akunnya?', 'Ya, hapus')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-400" title="Hapus"><x-ikon name="trash" class="w-4 h-4" /></button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-300">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada data karyawan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $employees->links() }}</div>
@endsection
