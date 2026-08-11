@extends('tata-letak.sidebar')

@section('title', 'Tambah Pengguna - ' . config('app.name'))
@section('page-title', 'Tambah Pengguna')

@section('content')
    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.pengguna.store') }}" class="space-y-5">
                @csrf

                <div>
                    <x-label-input for="name" :value="__('Nama Lengkap')" />
                    <x-input-teks id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Nama pengguna" class="mt-1 w-full" />
                    <x-kesalahan-input :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-label-input for="email" :value="__('Email (untuk login)')" />
                    <x-input-teks id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="mt-1 w-full" />
                    <x-kesalahan-input :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-label-input for="password" :value="__('Password Awal')" />
                    <x-input-teks id="password" type="text" name="password" value="{{ old('password') }}" required minlength="8" placeholder="Minimal 8 karakter" class="mt-1 w-full" />
                    <x-kesalahan-input :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-label-input for="role_id" :value="__('Peran / Hak Akses')" />
                        <select id="role_id" name="role_id" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">- Pilih Peran -</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->label }}</option>
                            @endforeach
                        </select>
                        <x-kesalahan-input :messages="$errors->get('role_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-label-input for="status" :value="__('Status Akun')" />
                        <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="active" @selected(old('status', 'active') === 'active')>Aktif</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                        </select>
                        <x-kesalahan-input :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-900 flex items-start gap-2">
                    <x-ikon name="info" class="w-4 h-4 shrink-0 mt-0.5" />
                    <span>Setelah ditambahkan, admin dapat mengaitkan akun ini dengan data karyawan lewat menu Data Karyawan.</span>
                </div>

                <div class="flex gap-3 pt-2">
                    <button class="flex-1 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold transition">Simpan</button>
                    <a href="{{ route('admin.pengguna.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
