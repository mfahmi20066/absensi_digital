@extends('tata-letak.sidebar')

@section('title', 'Lembur - ' . config('app.name'))
@section('page-title', 'Lembur (Overtime)')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Ajukan Lembur</h3>
                <form method="POST" action="{{ route('karyawan.lembur.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Tanggal Lembur</label>
                        <input type="date" name="date" value="{{ old('date', today()->toDateString()) }}" min="{{ today()->toDateString() }}" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Jam Mulai</label>
                            <input type="time" name="start_time" value="{{ old('start_time', '17:00') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Jam Selesai</label>
                            <input type="time" name="end_time" value="{{ old('end_time', '20:00') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Alasan / Uraian Pekerjaan</label>
                        <textarea name="reason" rows="3" required class="mt-1 w-full rounded-lg border-gray-300" placeholder="Tuliskan uraian pekerjaan lembur...">{{ old('reason') }}</textarea>
                    </div>
                    <button class="w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Kirim Pengajuan</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total Lembur Disetujui Tahun Ini</div>
                <div class="text-3xl font-bold text-blue-900 mt-1">
                    {{ intdiv($monthTotal, 60) }}<span class="text-lg"> jam</span> {{ $monthTotal % 60 }}<span class="text-lg"> menit</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Riwayat Pengajuan Lembur Saya</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs text-gray-500 uppercase">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3">Durasi</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($requests as $req)
                            <tr>
                                <td class="px-4 py-3 text-gray-700">{{ $req->date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $req->startTimeLabel }} - {{ $req->endTimeLabel }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $req->durationLabel }}</td>
                                <td class="px-4 py-3 max-w-[200px]">
                                    <div class="truncate text-gray-600" title="{{ $req->reason }}">{{ $req->reason }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                        {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : ($req->status === 'verified_by_admin' ? 'bg-blue-100 text-blue-900' : 'bg-amber-100 text-amber-700')) }}">
                                        {{ $req->statusLabel }}
                                    </span>
                                    @if ($req->rejection_note)
                                        <div class="text-xs text-red-500 mt-1" title="{{ $req->rejection_note }}">{{ Str::limit($req->rejection_note, 40) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($req->status === 'pending')
                                        <form method="POST" action="{{ route('karyawan.lembur.destroy', $req) }}" onsubmit="return confirmSubmit(this, 'Batalkan pengajuan lembur ini?', 'Ya, batalkan')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition">Batal</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada pengajuan lembur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-5 py-4">{{ $requests->links() }}</div>
            </div>
        </div>
    </div>
@endsection
