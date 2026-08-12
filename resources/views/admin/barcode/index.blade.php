@extends('tata-letak.sidebar')

@section('title', 'Barcode / QR - ' . config('app.name'))
@section('page-title', 'Barcode / QR Karyawan')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIP..." class="flex-1 rounded-lg border-gray-300">
            <button class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold">Cari</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Kode Barcode</th>
                    <th class="px-4 py-3">Berlaku</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($employees as $emp)
                    @php $bc = $emp->activeBarcode; @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-sm">{{ strtoupper(substr($emp->user?->name ?? '-', 0, 1)) }}</div>
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $emp->user?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $emp->nip }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($bc)
                                <span class="font-mono text-xs text-gray-600">{{ $bc->code }}</span>
                            @else
                                <span class="text-xs text-red-500 font-semibold">Belum ada barcode aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            @if ($bc)
                                {{ $bc->valid_from->format('d/m/Y') }} s/d {{ $bc->valid_until?->format('d/m/Y') ?? 'selamanya' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2 text-sm">
                                @if ($bc)
                                    <a href="{{ route('admin.barcode.print', $emp) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-900 font-semibold hover:bg-blue-100 inline-flex items-center gap-1.5"><x-ikon name="printer" class="w-4 h-4" /> Cetak</a>
                                    <a href="{{ route('admin.barcode.download', $emp) }}" class="px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 font-semibold hover:bg-gray-100 inline-flex items-center gap-1.5"><x-ikon name="download" class="w-4 h-4" /> PNG</a>
                                @endif
                                <form method="POST" action="{{ route('admin.barcode.generate', $emp) }}" onsubmit="return confirmSubmit(this, 'Buat barcode baru untuk {{ $emp->user?->name ?? '-' }}? Barcode lama otomatis nonaktif.', 'Ya, buat barcode')">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded-lg bg-gray-800 text-white font-semibold hover:bg-gray-900">Regenerasi</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada karyawan aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $employees->links() }}</div>
@endsection
