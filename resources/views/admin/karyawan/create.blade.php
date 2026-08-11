@extends('tata-letak.sidebar')

@section('title', 'Tambah Karyawan - ' . config('app.name'))
@section('page-title', 'Tambah Karyawan')

@section('content')
    @php
        $userOptions = $users->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role?->label ?? 'Karyawan',
        ])->values();
    @endphp

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2.5">
                <x-ikon name="user-plus" class="w-5 h-5 text-blue-900" />
                <div>
                    <h2 class="font-bold text-gray-800">Data Karyawan Baru</h2>
                    <p class="text-xs text-gray-500">Pilih akun yang sudah terdaftar, isi data kepegawaiannya.</p>
                </div>
            </div>

            <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.karyawan.store') : route('manajer.karyawan.store') }}" class="p-6 space-y-5">
                @csrf

                <div x-data="{
                    open: false,
                    query: '',
                    selected: null,
                    users: {{ Illuminate\Support\Js::from($userOptions) }},
                    get filtered() {
                        const q = this.query.toLowerCase();
                        return this.users.filter((u) => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
                    },
                    pilih(u) {
                        this.selected = u;
                        this.query = u.name + ' (' + u.email + ')';
                        this.open = false;
                    },
                }" class="relative">
                    <x-label-input for="user_id" :value="__('Akun Pengguna')" />
                    <div class="mt-1 relative">
                        <input type="hidden" name="user_id" :value="selected ? selected.id : ''">
                        <input
                            type="text"
                            value=""
                            x-model="query"
                            @focus="open = true"
                            @click="open = true"
                            @click.outside="open = false"
                            @keydown.escape="open = false"
                            @keydown.arrow-down.prevent="$refs.list.focus()"
                            placeholder="Cari nama atau email user..."
                            autocomplete="off"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10"
                        >
                        <x-ikon name="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                    </div>
                    <x-kesalahan-input :messages="$errors->get('user_id')" class="mt-1" />

                    <div
                        x-show="open"
                        x-transition.opacity
                        @keydown.arrow-up.prevent
                        @keydown.arrow-down.prevent
                        x-ref="list"
                        class="absolute z-20 mt-1 w-full max-h-56 overflow-auto bg-white rounded-xl shadow-lg border border-gray-100 py-1"
                        tabindex="-1"
                    >
                        <template x-if="filtered.length === 0">
                            <div class="px-4 py-3 text-sm text-gray-500">Tidak ada user yang cocok.</div>
                        </template>
                        <template x-for="u in filtered" :key="u.id">
                            <button
                                type="button"
                                @click="pilih(u)"
                                class="w-full text-left px-4 py-2.5 hover:bg-blue-50 flex items-center justify-between gap-2"
                                :class="selected && selected.id === u.id ? 'bg-blue-50' : ''"
                            >
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800" x-text="u.name"></span>
                                    <span class="block text-xs text-gray-500" x-text="u.email"></span>
                                </span>
                                <span class="shrink-0 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-gray-100 text-gray-600" x-text="u.role"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-label-input for="nip" :value="__('NIP')" />
                        <div class="mt-1 relative">
                            <x-input-teks id="nip" type="text" name="nip" value="{{ old('nip') }}" placeholder="Opsional — otomatis dari akun jika kosong" class="mt-1 w-full" />
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Barcode QR dibuat otomatis. Jika NIP kosong, kode dibuat dari ID akun.</p>
                        <x-kesalahan-input :messages="$errors->get('nip')" class="mt-1" />
                    </div>
                    <div>
                        <x-label-input for="position_id" :value="__('Jabatan')" />
                        <select id="position_id" name="position_id" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">- Pilih Jabatan -</option>
                            @foreach ($jabatans as $j)
                                <option value="{{ $j->id }}" @selected(old('position_id') == $j->id)>{{ $j->name }}</option>
                            @endforeach
                        </select>
                        <x-kesalahan-input :messages="$errors->get('position_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-label-input for="phone" :value="__('No. HP / WA')" />
                        <x-input-teks id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="mis. 0812xxxxxxx" class="mt-1 w-full" />
                        <x-kesalahan-input :messages="$errors->get('phone')" class="mt-1" />
                    </div>
                    <div>
                        <x-label-input for="work_schedule_id" :value="__('Shift / Jadwal Kerja')" />
                        <select id="work_schedule_id" name="work_schedule_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">- Pilih Shift -</option>
                            @foreach ($schedules as $s)
                                <option value="{{ $s->id }}" @selected(old('work_schedule_id') == $s->id)>{{ $s->name }} ({{ $s->time_in->format('H:i') }} - {{ $s->time_out->format('H:i') }})</option>
                            @endforeach
                        </select>
                        <x-kesalahan-input :messages="$errors->get('work_schedule_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-label-input for="join_date" :value="__('Tanggal Bergabung')" />
                        <x-input-teks id="join_date" type="date" name="join_date" value="{{ old('join_date', today()->toDateString()) }}" required class="mt-1 w-full" />
                        <x-kesalahan-input :messages="$errors->get('join_date')" class="mt-1" />
                    </div>
                    <div>
                        <x-label-input for="status" :value="__('Status Karyawan')" />
                        <select id="status" name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                            <option value="resign" @selected(old('status') === 'resign')>Resign</option>
                        </select>
                        <x-kesalahan-input :messages="$errors->get('status')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-label-input for="role_id" :value="__('Hak Akses (opsional)')" />
                        <select id="role_id" name="role_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tetap sesuai akun terdaftar</option>
                            <option value="3" @selected(old('role_id') === '3')>Karyawan</option>
                        </select>
                        <x-kesalahan-input :messages="$errors->get('role_id')" class="mt-1" />
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-900 flex items-start gap-2">
                    <x-ikon name="lightbulb" class="w-4 h-4 shrink-0 mt-0.5" />
                    <span>Barcode QR absensi dibuat otomatis — memakai NIP, atau kode dari ID akun jika NIP dikosongkan.</span>
                </div>

                <div class="flex gap-3 pt-2">
                    <button class="flex-1 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold transition">Simpan</button>
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.karyawan.index') : route('manajer.karyawan.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
