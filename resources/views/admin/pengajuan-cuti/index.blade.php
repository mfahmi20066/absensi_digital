@extends('tata-letak.sidebar')

@section('title', 'Pengajuan Izin - ' . config('app.name'))
@section('page-title', 'Pengajuan Izin / Sakit / Cuti')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="status" class="rounded-lg border-gray-300 sm:flex-1">
                <option value="all">Semua Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="verified_by_admin" @selected(request('status') === 'verified_by_admin')>Diverifikasi Admin</option>
                <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
            </select>
            <select name="type" class="rounded-lg border-gray-300 sm:flex-1">
                <option value="all">Semua Jenis</option>
                <option value="izin" @selected(request('type') === 'izin')>Izin</option>
                <option value="sakit" @selected(request('type') === 'sakit')>Sakit</option>
                <option value="cuti" @selected(request('type') === 'cuti')>Cuti</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold sm:w-auto">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Alasan / Bukti</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($requests as $req)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">{{ $req->employee?->user?->name ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $req->employee?->nip ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $req->type === 'sakit' ? 'bg-red-100 text-red-700' : ($req->type === 'cuti' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700') }}">
                                {{ $req->typeLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $req->start_date->format('d/m/Y') }} - {{ $req->end_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 max-w-[220px]">
                            <div class="truncate text-gray-600" title="{{ $req->reason }}">{{ $req->reason }}</div>
                            @if ($req->attachment)
                                <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" class="text-xs text-blue-900 hover:underline inline-flex items-center gap-1"><x-ikon name="paperclip" class="w-3.5 h-3.5" /> lihat bukti</a>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : ($req->status === 'verified_by_admin' ? 'bg-blue-100 text-blue-900' : 'bg-amber-100 text-amber-700')) }}">
                                {{ $req->statusLabel }}
                            </span>
                            @if ($req->rejection_note)
                                <div class="text-xs text-red-500 mt-1" title="{{ $req->rejection_note }}">{{ Str::limit($req->rejection_note, 40) }}</div>
                            @endif
                            @if ($req->approver)
                                <div class="text-xs text-gray-400 mt-1">oleh {{ $req->approver->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $isAdmin = auth()->user()->isAdmin();
                                $isManajer = auth()->user()->isManajer();
                                $canFinal = $isAdmin && in_array($req->status, ['pending', 'verified_by_admin'], true)
                                    || $isManajer && $req->status === 'verified_by_admin';
                            @endphp
                            @if ($isAdmin && $req->status === 'pending')
                                <div class="flex justify-end gap-2">
                                    <a href="{{ $isAdmin ? route('admin.pengajuan-cuti.show', $req) : route('manajer.pengajuan-cuti.show', $req) }}" class="px-3 py-1.5 rounded-lg border border-blue-900/25 bg-blue-50 text-blue-900 text-xs font-semibold hover:bg-blue-100 transition inline-flex items-center gap-1"><x-ikon name="file-text" class="w-3.5 h-3.5" /> Detail</a>
                                    <form method="POST" action="{{ route('admin.pengajuan-cuti.verify', $req) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-lg bg-gray-700 text-white text-xs font-semibold hover:bg-gray-800 inline-flex items-center gap-1"><x-ikon name="check" class="w-3.5 h-3.5" /> Verifikasi</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pengajuan-cuti.approve', $req) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-lg bg-blue-900 text-white text-xs font-semibold hover:bg-blue-950 inline-flex items-center gap-1"><x-ikon name="check" class="w-3.5 h-3.5" /> Setujui</button>
                                    </form>
                                    <button onclick="openReject({{ $req->id }})" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 inline-flex items-center gap-1"><x-ikon name="x" class="w-3.5 h-3.5" /> Tolak</button>
                                </div>
                            @elseif ($canFinal)
                                <div class="flex justify-end gap-2">
                                    <a href="{{ $isAdmin ? route('admin.pengajuan-cuti.show', $req) : route('manajer.pengajuan-cuti.show', $req) }}" class="px-3 py-1.5 rounded-lg border border-blue-900/25 bg-blue-50 text-blue-900 text-xs font-semibold hover:bg-blue-100 transition inline-flex items-center gap-1"><x-ikon name="file-text" class="w-3.5 h-3.5" /> Detail</a>
                                    <form method="POST" action="{{ $isAdmin ? route('admin.pengajuan-cuti.approve', $req) : route('manajer.pengajuan-cuti.approve', $req) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-lg bg-blue-900 text-white text-xs font-semibold hover:bg-blue-950 inline-flex items-center gap-1"><x-ikon name="check" class="w-3.5 h-3.5" /> Setujui Final</button>
                                    </form>
                                    <button onclick="openReject({{ $req->id }})" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 inline-flex items-center gap-1"><x-ikon name="x" class="w-3.5 h-3.5" /> Tolak</button>
                                </div>
                            @else
                                <div class="flex justify-end">
                                    <a href="{{ $isAdmin ? route('admin.pengajuan-cuti.show', $req) : route('manajer.pengajuan-cuti.show', $req) }}" class="px-3 py-1.5 rounded-lg border border-blue-900/25 bg-blue-50 text-blue-900 text-xs font-semibold hover:bg-blue-100 transition inline-flex items-center gap-1"><x-ikon name="file-text" class="w-3.5 h-3.5" /> Detail</a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>

    <div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-6">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="font-bold text-gray-800 mb-4">Tolak Pengajuan</h3>
            <form method="POST" id="reject-form" class="space-y-4">
                @csrf
                <textarea name="rejection_note" rows="3" required class="w-full rounded-lg border-gray-300" placeholder="Alasan penolakan (wajib diisi)..."></textarea>
                <div class="flex gap-3">
                    <button class="flex-1 py-2.5 rounded-xl bg-red-600 text-white font-semibold">Tolak</button>
                    <button type="button" onclick="closeReject()" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold">Batal</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openReject(id) {
            document.getElementById('reject-form').action = "{{ auth()->user()->isAdmin() ? route('admin.pengajuan-cuti.reject', ['leaveRequest' => '__ID__']) : route('manajer.pengajuan-cuti.reject', ['leaveRequest' => '__ID__']) }}".replace('__ID__', id);
            document.getElementById('reject-modal').classList.remove('hidden');
            document.getElementById('reject-modal').classList.add('flex');
        }
        function closeReject() {
            document.getElementById('reject-modal').classList.add('hidden');
            document.getElementById('reject-modal').classList.remove('flex');
        }
    </script>
@endpush
