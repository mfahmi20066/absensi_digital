@extends('tata-letak.sidebar')

@section('title', 'Edit Pengguna - ' . config('app.name'))
@section('page-title', 'Edit Pengguna')

@section('content')
    <div class="max-w-2xl space-y-5">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.pengguna.update', $pengguna) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-label-input for="name" :value="__('Nama Lengkap')" />
                    <x-input-teks id="name" type="text" name="name" value="{{ old('name', $pengguna->name) }}" required class="mt-1 w-full" />
                    <x-kesalahan-input :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-label-input for="email" :value="__('Email (untuk login)')" />
                    <x-input-teks id="email" type="email" name="email" value="{{ old('email', $pengguna->email) }}" required class="mt-1 w-full" />
                    <x-kesalahan-input :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-label-input for="password" :value="__('Password Baru (opsional)')" />
                    <x-input-teks id="password" type="text" name="password" minlength="8" placeholder="Kosongkan jika tidak diganti" class="mt-1 w-full" />
                    <x-kesalahan-input :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-label-input for="role_id" :value="__('Peran / Hak Akses')" />
                        <select id="role_id" name="role_id" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id', $pengguna->role_id) == $role->id)>{{ $role->label }}</option>
                            @endforeach
                        </select>
                        <x-kesalahan-input :messages="$errors->get('role_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-label-input for="status" :value="__('Status Akun')" />
                        <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="active" @selected(old('status', $pengguna->status) === 'active')>Aktif</option>
                            <option value="pending" @selected(old('status', $pengguna->status) === 'pending')>Menunggu</option>
                            <option value="inactive" @selected(old('status', $pengguna->status) === 'inactive')>Nonaktif</option>
                        </select>
                        <x-kesalahan-input :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button class="flex-1 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold transition">Simpan Perubahan</button>
                    <a href="{{ route('admin.pengguna.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">Batal</a>
                </div>
            </form>
        </div>

        @if ($pengguna->id !== auth()->id())
        <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col sm:flex-row gap-3">
            <form method="POST" action="{{ route('admin.pengguna.toggle-status', $pengguna) }}" class="flex-1">
                @csrf
                <button class="w-full py-2.5 rounded-xl {{ $pengguna->status === 'active' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }} font-semibold text-sm inline-flex items-center justify-center gap-2">
                    <x-ikon name="{{ $pengguna->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                    {{ $pengguna->status === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.pengguna.reset-password', $pengguna) }}" class="flex-1" onsubmit="return confirmSubmit(this, 'Reset password akun ini menjadi password acak baru?', 'Ya, reset password')">
                @csrf
                <button class="w-full py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 font-semibold text-sm inline-flex items-center justify-center gap-2"><x-ikon name="key" class="w-4 h-4" /> Reset Password</button>
            </form>
        </div>
        @endif
    </div>
@endsection
