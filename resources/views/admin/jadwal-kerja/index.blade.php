@extends('tata-letak.sidebar')

@section('title', 'Jadwal Kerja - ' . config('app.name'))
@section('page-title', 'Jadwal Kerja')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Tambah Jadwal</h3>
                <form method="POST" action="{{ route('admin.jadwal-kerja.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Nama Shift *</label>
                        <input type="text" name="name" required class="mt-1 w-full rounded-lg border-gray-300" placeholder="mis. Shift Pagi">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Jam Masuk *</label>
                            <input type="time" name="time_in" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500">Jam Pulang *</label>
                            <input type="time" name="time_out" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Toleransi Telat (menit) *</label>
                        <input type="number" name="tolerance_minutes" value="15" min="0" max="180" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <button class="w-full py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Simpan</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Daftar Jadwal / Shift</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs text-gray-500 uppercase">
                            <th class="px-4 py-3">Shift</th>
                            <th class="px-4 py-3">Jam Masuk</th>
                            <th class="px-4 py-3">Jam Pulang</th>
                            <th class="px-4 py-3">Toleransi</th>
                            <th class="px-4 py-3">Karyawan</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($schedules as $s)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $s->name }}</td>
                                <td class="px-4 py-3">{{ $s->time_in->format('H:i') }}</td>
                                <td class="px-4 py-3">{{ $s->time_out->format('H:i') }}</td>
                                <td class="px-4 py-3">{{ $s->tolerance_minutes }} menit</td>
                                <td class="px-4 py-3">{{ $s->employees_count }} orang</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="editSchedule(this)" data-id="{{ $s->id }}" data-name="{{ $s->name }}" data-time-in="{{ $s->time_in->format('H:i') }}" data-time-out="{{ $s->time_out->format('H:i') }}" data-tolerance="{{ $s->tolerance_minutes }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"><x-ikon name="pencil" class="w-4 h-4" /></button>
                                        <form method="POST" action="{{ route('admin.jadwal-kerja.destroy', $s) }}" onsubmit="return confirmSubmit(this, 'Hapus jadwal {{ $s->name }}?', 'Ya, hapus')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-400"><x-ikon name="trash" class="w-4 h-4" /></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada jadwal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-6">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <h3 class="font-bold text-gray-800 mb-4">Edit Jadwal</h3>
            <form method="POST" id="edit-form" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-semibold text-gray-500">Nama Shift *</label>
                    <input type="text" name="name" id="edit-name" required class="mt-1 w-full rounded-lg border-gray-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Jam Masuk *</label>
                        <input type="time" name="time_in" id="edit-time-in" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Jam Pulang *</label>
                        <input type="time" name="time_out" id="edit-time-out" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Toleransi Telat (menit) *</label>
                    <input type="number" name="tolerance_minutes" id="edit-tolerance" min="0" max="180" required class="mt-1 w-full rounded-lg border-gray-300">
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
        function editSchedule(btn) {
            const id = btn.dataset.id;
            document.getElementById('edit-form').action = "{{ route('admin.jadwal-kerja.update', ['jadwal_kerja' => '__ID__']) }}".replace('__ID__', id);
            document.getElementById('edit-name').value = btn.dataset.name;
            document.getElementById('edit-time-in').value = btn.dataset.timeIn;
            document.getElementById('edit-time-out').value = btn.dataset.timeOut;
            document.getElementById('edit-tolerance').value = btn.dataset.tolerance;
            document.getElementById('edit-modal').classList.remove('hidden');
            document.getElementById('edit-modal').classList.add('flex');
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
            document.getElementById('edit-modal').classList.remove('flex');
        }
    </script>
@endpush
