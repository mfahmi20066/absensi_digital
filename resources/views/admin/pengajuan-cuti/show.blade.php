@extends('tata-letak.sidebar')

@section('title', 'Detail Pengajuan - ' . config('app.name'))
@section('page-title', 'Detail Pengajuan')

@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $isManajer = auth()->user()->isManajer();
        $durasi = $req->start_date->diffInDays($req->end_date) + 1;
        $backRoute = $isAdmin ? route('admin.pengajuan-cuti.index') : route('manajer.pengajuan-cuti.index');
        $canFinal = $isAdmin && in_array($req->status, ['pending', 'verified_by_admin'], true)
            || $isManajer && $req->status === 'verified_by_admin';
        $attachmentImage = $req->attachment && in_array(strtolower(pathinfo($req->attachment, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
    @endphp

    <div class="max-w-3xl space-y-5">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <x-ikon name="file-text" class="w-5 h-5 text-blue-900" />
                    <div>
                        <h2 class="font-bold text-gray-800">Detail Pengajuan</h2>
                        <p class="text-xs text-gray-500">Diajukan {{ $req->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>
                <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition">
                    <x-ikon name="arrow-right" class="w-4 h-4 rotate-180" /> Kembali
                </a>
            </div>

            <div class="p-6 space-y-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                        {{ $req->type === 'sakit' ? 'bg-red-100 text-red-700' : ($req->type === 'cuti' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700') }}">
                        <x-ikon name="calendar" class="w-3.5 h-3.5" /> {{ $req->typeLabel }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                        {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : ($req->status === 'verified_by_admin' ? 'bg-blue-100 text-blue-900' : 'bg-amber-100 text-amber-700')) }}">
                        {{ $req->statusLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="user" class="w-3.5 h-3.5" /> Karyawan
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">{{ $req->employee->user->name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $req->employee->nip ?? '-' }} &middot; {{ $req->employee->jabatan?->name ?? $req->employee->position ?? '-' }}
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="phone" class="w-3.5 h-3.5" /> No. HP / WA
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">{{ $req->employee->phone ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="calendar" class="w-3.5 h-3.5" /> Periode
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">
                            {{ $req->start_date->translatedFormat('d M Y') }} - {{ $req->end_date->translatedFormat('d M Y') }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $durasi }} hari</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="clock" class="w-3.5 h-3.5" /> Diproses
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">
                            {{ $req->approved_at?->translatedFormat('d M Y, H:i') ?? 'Belum diproses' }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $req->approver?->name ?? '-' }}</div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <x-ikon name="file-text" class="w-3.5 h-3.5" /> Alasan
                    </div>
                    <p class="mt-2 text-sm text-gray-700 bg-gray-50 rounded-xl p-4 whitespace-pre-wrap">{{ $req->reason }}</p>
                </div>

                @if ($req->rejection_note)
                    <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3.5 text-sm text-red-700 flex items-start gap-2.5">
                        <x-ikon name="x-circle" class="w-4 h-4 shrink-0 mt-0.5" />
                        <div>
                            <div class="font-bold">Alasan Penolakan</div>
                            <div class="mt-0.5">{{ $req->rejection_note }}</div>
                        </div>
                    </div>
                @endif

                @if ($req->attachment)
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="paperclip" class="w-3.5 h-3.5" /> Bukti Lampiran
                        </div>
                        <div class="mt-2 flex items-center gap-3 bg-gray-50 rounded-xl p-4">
                            <div class="w-12 h-12 rounded-lg {{ $attachmentImage ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-600' }} flex items-center justify-center shrink-0">
                                <x-ikon name="{{ $attachmentImage ? 'image' : 'file-text' }}" class="w-6 h-6" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-800">{{ basename($req->attachment) }}</div>
                                <div class="text-xs text-gray-500">{{ $attachmentImage ? 'Gambar (JPG/PNG)' : 'Dokumen PDF' }}</div>
                            </div>
                            <button onclick="openBukti()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-xs font-semibold transition shrink-0">
                                <x-ikon name="search" class="w-3.5 h-3.5" /> Lihat Bukti
                            </button>
                        </div>
                    </div>
                @endif

                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                        <x-ikon name="history" class="w-3.5 h-3.5" /> Riwayat Status
                    </div>
                    <div class="space-y-0">
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></div>
                                <div class="w-px flex-1 bg-gray-200 mt-1"></div>
                            </div>
                            <div class="pb-4">
                                <div class="text-sm font-semibold text-gray-800">Diajukan</div>
                                <div class="text-xs text-gray-500">{{ $req->created_at->translatedFormat('d M Y, H:i') }} oleh {{ $req->employee->user->name }}</div>
                            </div>
                        </div>

                        @if (in_array($req->status, ['verified_by_admin', 'approved', 'rejected'], true))
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-3.5 h-3.5 rounded-full {{ $req->status !== 'verified_by_admin' ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-blue-600 ring-4 ring-blue-100' }}"></div>
                                    <div class="w-px flex-1 bg-gray-200 mt-1"></div>
                                </div>
                                <div class="pb-4">
                                    <div class="text-sm font-semibold text-gray-800">Diverifikasi Admin</div>
                                    <div class="text-xs text-gray-500">{{ $req->approved_at?->translatedFormat('d M Y, H:i') ?? '-' }} oleh {{ $req->approver?->name ?? '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-3.5 h-3.5 rounded-full bg-gray-300"></div>
                                    <div class="w-px flex-1 bg-gray-200 mt-1"></div>
                                </div>
                                <div class="pb-4">
                                    <div class="text-sm font-semibold text-gray-400">Diverifikasi Admin</div>
                                    <div class="text-xs text-gray-400">Menunggu verifikasi admin</div>
                                </div>
                            </div>
                        @endif

                        @if (in_array($req->status, ['approved', 'rejected'], true))
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-3.5 h-3.5 rounded-full {{ $req->status === 'approved' ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-red-500 ring-4 ring-red-100' }}"></div>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">{{ $req->status === 'approved' ? 'Disetujui' : 'Ditolak' }}</div>
                                    <div class="text-xs text-gray-500">{{ $req->approved_at?->translatedFormat('d M Y, H:i') ?? '-' }} oleh {{ $req->approver?->name ?? '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-3.5 h-3.5 rounded-full bg-gray-300"></div>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-400">Keputusan Final</div>
                                    <div class="text-xs text-gray-400">{{ $req->status === 'verified_by_admin' ? 'Menunggu persetujuan manajer' : 'Menunggu proses selanjutnya' }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($isAdmin && $req->status === 'pending' || $canFinal)
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex flex-wrap gap-3">
                    @if ($isAdmin && $req->status === 'pending')
                        <form method="POST" action="{{ route('admin.pengajuan-cuti.verify', $req) }}">
                            @csrf
                            <button class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold transition">
                                <x-ikon name="check" class="w-4 h-4" /> Verifikasi
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ $isAdmin ? route('admin.pengajuan-cuti.approve', $req) : route('manajer.pengajuan-cuti.approve', $req) }}">
                        @csrf
                        <button class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold transition">
                            <x-ikon name="check" class="w-4 h-4" /> Setujui Final
                        </button>
                    </form>
                    <button onclick="openReject({{ $req->id }})" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold transition">
                        <x-ikon name="x" class="w-4 h-4" /> Tolak
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div id="bukti-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-6">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2 text-sm font-bold text-gray-800 min-w-0">
                    <x-ikon name="paperclip" class="w-4 h-4 text-blue-900 shrink-0" />
                    <span class="truncate">{{ basename($req->attachment) }}</span>
                </div>
                <button onclick="closeBukti()" class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 shrink-0" aria-label="Tutup">
                    <x-ikon name="x" class="w-5 h-5" />
                </button>
            </div>
            @if ($attachmentImage)
                <img src="{{ asset('storage/' . $req->attachment) }}" alt="Bukti lampiran" class="w-full max-h-[70vh] object-contain rounded-xl border border-gray-200">
            @else
                <iframe src="{{ asset('storage/' . $req->attachment) }}" class="w-full h-[70vh] rounded-xl border border-gray-200 bg-gray-100"></iframe>
            @endif
            <a href="{{ asset('storage/' . $req->attachment) }}" target="_blank" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition">
                <x-ikon name="download" class="w-4 h-4" /> Buka di Tab Baru
            </a>
        </div>
    </div>

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
        function openBukti() {
            const modal = document.getElementById('bukti-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.addEventListener('keydown', closeBuktiOnEsc);
        }
        function closeBukti() {
            const modal = document.getElementById('bukti-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.removeEventListener('keydown', closeBuktiOnEsc);
        }
        function closeBuktiOnEsc(e) {
            if (e.key === 'Escape') closeBukti();
        }
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
