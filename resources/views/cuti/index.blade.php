@extends('tata-letak.sidebar')

@section('title', 'Izin / Sakit / Cuti - ' . config('app.name'))
@section('page-title', 'Izin / Sakit / Cuti')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500 uppercase font-semibold">Sisa Cuti Tahunan</div>
                        <div class="text-3xl font-bold text-violet-600 mt-1">{{ $sisaCuti }} <span class="text-sm text-gray-400">hari</span></div>
                        <div class="text-xs text-gray-500 mt-1">jatah tahun {{ now()->year }}</div>
                    </div>
                    <x-ikon name="calendar" class="w-10 h-10 text-violet-200" />
                </div>
                <p class="mt-3 text-xs text-gray-400">Kuota cuti otomatis dipotong saat pengajuan cuti disetujui, dan direset tiap awal tahun.</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Ajukan Baru</h3>
                <form method="POST" action="{{ route('karyawan.cuti.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Jenis</label>
                        <select name="type" class="mt-1 w-full rounded-lg border-gray-300" required>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti">Cuti</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="mt-1 w-full rounded-lg border-gray-300" required>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="mt-1 w-full rounded-lg border-gray-300" required>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Alasan</label>
                        <textarea name="reason" rows="3" class="mt-1 w-full rounded-lg border-gray-300" required placeholder="Tuliskan alasan pengajuan..."></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Bukti (surat dokter, dll) - opsional</label>
                        <div class="mt-1" x-data="uploadBukti()">
                            <input type="file" name="attachment" accept="image/*,.pdf" x-ref="fileInput" class="hidden" @change="onPilih($event.target.files[0])">

                            <template x-if="!file">
                                <div
                                    @click="$refs.fileInput.click()"
                                    @dragover.prevent="dragOver = true"
                                    @dragleave.prevent="dragOver = false"
                                    @drop.prevent="onDrop($event)"
                                    :class="dragOver ? 'border-blue-500 bg-blue-50' : 'border-dashed border-gray-300 bg-gray-50 hover:border-blue-400 hover:bg-blue-50/50'"
                                    class="border-2 rounded-xl px-6 py-8 flex flex-col items-center justify-center text-center cursor-pointer transition"
                                >
                                    <div :class="dragOver ? 'bg-blue-100 text-blue-900' : 'bg-gray-100 text-gray-400'" class="w-12 h-12 rounded-full flex items-center justify-center mb-3 transition">
                                        <x-ikon name="upload" class="w-6 h-6" />
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700">
                                        Seret &amp; letakkan file di sini
                                    </p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        atau <span class="text-blue-900 font-semibold underline">klik untuk memilih</span>
                                    </p>
                                    <p class="mt-2 text-[11px] text-gray-400">JPG, PNG, atau PDF &middot; maksimal 2 MB</p>
                                </div>
                            </template>

                            <template x-if="file">
                                <div class="border border-gray-200 rounded-xl bg-gray-50 p-3 flex items-center gap-3">
                                    <template x-if="file.isImage">
                                        <img :src="file.preview" alt="Pratinjau bukti" class="w-14 h-14 rounded-lg object-cover border border-gray-200">
                                    </template>
                                    <template x-if="!file.isImage">
                                        <div class="w-14 h-14 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                            <x-ikon name="file-text" class="w-6 h-6" />
                                        </div>
                                    </template>
                                    <div class="flex-1 min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-800" x-text="file.name"></div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            <span x-text="file.size"></span>
                                            <span x-show="file.isImage"> &middot; pratinjau</span>
                                        </div>
                                    </div>
                                    <button type="button" @click="hapus()" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition shrink-0" title="Hapus file">
                                        <x-ikon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            </template>

                            <p x-show="error" x-text="error" class="mt-1.5 text-xs text-red-600 font-medium"></p>
                        </div>
                    </div>
                    <button class="w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Kirim Pengajuan</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm table-scroll-wrapper">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Riwayat Pengajuan Saya</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs text-gray-500 uppercase">
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Periode</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($requests as $req)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $req->type === 'sakit' ? 'bg-red-100 text-red-700' : ($req->type === 'cuti' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700') }}">
                                        {{ $req->typeLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $req->start_date->format('d/m/Y') }} - {{ $req->end_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 max-w-[200px]">
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
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('karyawan.cuti.show', $req) }}" class="px-2.5 py-1 rounded-lg border border-blue-900/25 bg-blue-50 text-blue-900 text-xs font-semibold hover:bg-blue-100 transition inline-flex items-center gap-1"><x-ikon name="file-text" class="w-3.5 h-3.5" /> Detail</a>
                                        @if ($req->status === 'pending')
                                            <form method="POST" action="{{ route('karyawan.cuti.destroy', $req) }}" onsubmit="return confirmSubmit(this, 'Batalkan pengajuan ini?', 'Ya, batalkan')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition">Batal</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada pengajuan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    <div class="mt-4">{{ $requests->links() }}</div>
    </div>
@endsection

@push('scripts')
    <script>
        function uploadBukti() {
            return {
                file: null,
                dragOver: false,
                error: '',
                validTypes: ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'],
                validExt: ['jpg', 'jpeg', 'png', 'pdf'],
                maxBytes: 2 * 1024 * 1024,

                onDrop(e) {
                    this.dragOver = false;
                    const f = e.dataTransfer.files[0];
                    if (f) this.onPilih(f);
                },

                onPilih(f) {
                    this.error = '';

                    if (!f) return;

                    const ext = (f.name.split('.').pop() || '').toLowerCase();
                    if (!this.validTypes.includes(f.type) && !this.validExt.includes(ext)) {
                        this.error = 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF.';
                        this.$refs.fileInput.value = '';
                        return;
                    }

                    if (f.size > this.maxBytes) {
                        this.error = 'Ukuran file melebihi 2 MB.';
                        this.$refs.fileInput.value = '';
                        return;
                    }

                    this.file = {
                        name: f.name,
                        size: this.formatSize(f.size),
                        isImage: f.type.startsWith('image/'),
                        preview: f.type.startsWith('image/') ? URL.createObjectURL(f) : null,
                    };
                },

                hapus() {
                    if (this.file && this.file.preview) URL.revokeObjectURL(this.file.preview);
                    this.file = null;
                    this.error = '';
                    this.$refs.fileInput.value = '';
                },

                formatSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
                },
            };
        }
    </script>
@endpush
