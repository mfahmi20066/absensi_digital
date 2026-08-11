@extends('tata-letak.sidebar')

@section('title', 'Kelola Jabatan - ' . config('app.name'))
@section('page-title', 'Kelola Jabatan')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Tambah Jabatan</h3>
                <form method="POST" action="{{ route('admin.jabatan.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Nama Jabatan *</label>
                        <input type="text" name="name" required class="mt-1 w-full rounded-lg border-gray-300" placeholder="mis. Kepala SPPG">
                    </div>
                    <button class="w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Simpan</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Daftar Jabatan</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs text-gray-500 uppercase">
                            <th class="px-4 py-3">Jabatan</th>
                            <th class="px-4 py-3">Karyawan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($jabatans as $j)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $j->name }}</td>
                                <td class="px-4 py-3">{{ $j->karyawan_count }} orang</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="editJabatan(this)" data-id="{{ $j->id }}" data-name="{{ $j->name }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500" title="Edit"><x-ikon name="pencil" class="w-4 h-4" /></button>
                                        @if ($j->karyawan_count === 0)
                                            <form method="POST" action="{{ route('admin.jabatan.destroy', $j) }}" onsubmit="return confirmSubmit(this, 'Hapus jabatan {{ $j->name }}?', 'Ya, hapus')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-400" title="Hapus"><x-ikon name="trash" class="w-4 h-4" /></button>
                                            </form>
                                        @else
                                            <button class="p-1.5 rounded-lg text-gray-300 cursor-not-allowed" title="Tidak bisa dihapus, masih dipakai karyawan"><x-ikon name="trash" class="w-4 h-4" /></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Belum ada jabatan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-6">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="font-bold text-gray-800 mb-4">Edit Jabatan</h3>
            <form method="POST" id="edit-form" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-semibold text-gray-500">Nama Jabatan *</label>
                    <input type="text" name="name" id="edit-name" required class="mt-1 w-full rounded-lg border-gray-300">
                </div>
                <div class="flex gap-3">
                    <button class="flex-1 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Simpan</button>
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold">Tutup</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function editJabatan(btn) {
            const id = btn.dataset.id;
            document.getElementById('edit-form').action = "{{ route('admin.jabatan.update', ['jabatan' => '__ID__']) }}".replace('__ID__', id);
            document.getElementById('edit-name').value = btn.dataset.name;
            document.getElementById('edit-modal').classList.remove('hidden');
            document.getElementById('edit-modal').classList.add('flex');
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
            document.getElementById('edit-modal').classList.remove('flex');
        }
    </script>
@endpush
