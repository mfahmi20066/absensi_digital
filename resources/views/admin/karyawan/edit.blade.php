@extends('tata-letak.sidebar')

@section('title', 'Edit Karyawan - ' . config('app.name'))
@section('page-title', 'Edit Karyawan')

@section('content')
    <div class="max-w-2xl space-y-5">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.karyawan.update', $employee) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-gray-500">Nama Lengkap *</label>
                        <input type="text" name="name" value="{{ old('name', $employee->user?->name ?? '') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Email (untuk login) *</label>
                        <input type="email" name="email" value="{{ old('email', $employee->user?->email ?? '') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Password Baru (kosongkan jika tidak diganti)</label>
                        <input type="text" name="password" minlength="8" class="mt-1 w-full rounded-lg border-gray-300" placeholder="Kosongkan jika tidak diganti">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">NIP *</label>
                        <input type="text" name="nip" value="{{ old('nip', $employee->nip) }}" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Jabatan *</label>
                        <select name="position_id" required class="mt-1 w-full rounded-lg border-gray-300">
                            <option value="">- Pilih Jabatan -</option>
                            @foreach ($jabatans as $j)
                                <option value="{{ $j->id }}" @selected(old('position_id', $employee->position_id) == $j->id)>{{ $j->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">No. HP / WA</label>
                        <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Shift / Jadwal Kerja</label>
                        <select name="work_schedule_id" class="mt-1 w-full rounded-lg border-gray-300">
                            <option value="">- Pilih Shift -</option>
                            @foreach ($schedules as $s)
                                <option value="{{ $s->id }}" @selected($employee->work_schedule_id === $s->id)>{{ $s->name }} ({{ $s->time_in->format('H:i') }} - {{ $s->time_out->format('H:i') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Tanggal Bergabung *</label>
                        <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date?->toDateString()) }}" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Status Karyawan *</label>
                        <select name="status" class="mt-1 w-full rounded-lg border-gray-300">
                            @foreach (['aktif', 'nonaktif', 'resign'] as $st)
                                <option value="{{ $st }}" @selected($employee->status === $st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button class="flex-1 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Simpan Perubahan</button>
                    <a href="{{ route('admin.karyawan.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold">Batal</a>
                </div>
            </form>
        </div>

        @if (auth()->user()->isAdmin())
        <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col sm:flex-row gap-3">
            <form method="POST" action="{{ route('admin.karyawan.toggle-status', $employee) }}" class="flex-1">
                @csrf
                <button class="w-full py-2.5 rounded-xl {{ $employee->user?->status === 'active' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }} font-semibold text-sm inline-flex items-center justify-center gap-2">
                    <x-ikon name="{{ $employee->user?->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                    {{ $employee->user?->status === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.karyawan.reset-password', $employee) }}" class="flex-1" onsubmit="return confirmSubmit(this, 'Reset password akun ini menjadi password123?', 'Ya, reset password')">
                @csrf
                <button class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold text-sm inline-flex items-center justify-center gap-2"><x-ikon name="key" class="w-4 h-4" /> Reset Password</button>
            </form>
            <a href="{{ route('admin.barcode.print', $employee) }}" class="flex-1 py-2.5 rounded-xl bg-blue-50 text-blue-900 hover:bg-blue-100 font-semibold text-sm text-center inline-flex items-center justify-center gap-2"><x-ikon name="qrcode" class="w-4 h-4" /> Cetak Barcode</a>
        </div>
        @endif
    </div>
@endsection
