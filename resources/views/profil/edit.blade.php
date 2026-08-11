@extends('tata-letak.sidebar')

@section('title', 'Profil Saya - ' . config('app.name'))
@section('page-title', 'Profil Saya')

@section('content')
    @php
        $user = auth()->user();
        $employee = $user->employee;
        $initial = strtoupper(substr($user->name, 0, 1));
        $roleLabel = $user->role?->label ?? 'Pengguna';
    @endphp

    <div class="max-w-4xl space-y-5">
        <div class="bg-gradient-to-r from-blue-950 via-blue-900 to-blue-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 sm:px-8 py-6 sm:py-8 flex flex-col sm:flex-row items-center sm:items-center gap-5">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold shadow-inner">
                    {{ $initial }}
                </div>
                <div class="text-center sm:text-left flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-white">{{ $user->name }}</h2>
                    <div class="mt-1 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-white text-xs font-semibold border border-white/20">
                            <x-ikon name="shield" class="w-3.5 h-3.5" />
                            {{ $roleLabel }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $user->status === 'active' ? 'bg-emerald-400/20 text-emerald-200 border border-emerald-300/30' : 'bg-red-400/20 text-red-200 border border-red-300/30' }} text-xs font-semibold">
                            <x-ikon name="{{ $user->status === 'active' ? 'check-circle' : 'x-circle' }}" class="w-3.5 h-3.5" />
                            {{ $user->status === 'active' ? 'Akun Aktif' : 'Akun Nonaktif' }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-center justify-center sm:justify-start gap-2 text-blue-200 text-sm">
                        <x-ikon name="user" class="w-4 h-4" />
                        {{ $user->email }}
                    </div>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('dasbor') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-semibold transition">
                        <x-ikon name="home" class="w-4 h-4" />
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>

        @if ($employee)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2.5">
                    <x-ikon name="clipboard" class="w-5 h-5 text-blue-900" />
                    <div>
                        <h2 class="font-bold text-gray-800">Data Kepegawaian</h2>
                        <p class="text-xs text-gray-500">Informasi kepegawaian Anda terdaftar di sistem.</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="user" class="w-3.5 h-3.5" /> NIP
                        </div>
                        <div class="mt-1.5 font-mono text-sm font-semibold text-gray-800 break-all">{{ $employee->nip ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="clipboard" class="w-3.5 h-3.5" /> Jabatan
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">{{ $employee->jabatan?->name ?? $employee->position ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="clock" class="w-3.5 h-3.5" /> Shift
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">
                            @if ($employee->workSchedule)
                                {{ $employee->workSchedule->name }}
                                <span class="block text-xs font-normal text-gray-500">
                                    {{ $employee->workSchedule->time_in->format('H:i') }} - {{ $employee->workSchedule->time_out->format('H:i') }}
                                </span>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="phone" class="w-3.5 h-3.5" /> No. HP / WA
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">{{ $employee->phone ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="calendar" class="w-3.5 h-3.5" /> Bergabung
                        </div>
                        <div class="mt-1.5 text-sm font-semibold text-gray-800">{{ $employee->join_date?->translatedFormat('d M Y') ?? '-' }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <x-ikon name="check-circle" class="w-3.5 h-3.5" /> Status
                        </div>
                        <div class="mt-1.5">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $employee->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : ($employee->status === 'nonaktif' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2.5">
                <x-ikon name="user" class="w-5 h-5 text-blue-900" />
                <div>
                    <h2 class="font-bold text-gray-800">Informasi Akun</h2>
                    <p class="text-xs text-gray-500">Perbarui nama lengkap dan alamat email Anda.</p>
                </div>
            </div>
            <div class="p-6">
                @include('profil.bagian.perbarui-info-akun')
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2.5">
                <x-ikon name="key" class="w-5 h-5 text-blue-900" />
                <div>
                    <h2 class="font-bold text-gray-800">Keamanan & Kata Sandi</h2>
                    <p class="text-xs text-gray-500">Ganti kata sandi Anda secara berkala demi keamanan akun.</p>
                </div>
            </div>
            <div class="p-6">
                @include('profil.bagian.perbarui-sandi')
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-50 flex items-center gap-2.5">
                <x-ikon name="trash" class="w-5 h-5 text-red-500" />
                <div>
                    <h2 class="font-bold text-gray-800">Hapus Akun</h2>
                    <p class="text-xs text-gray-500">Hapus akun Anda beserta seluruh datanya secara permanen.</p>
                </div>
            </div>
            <div class="p-6">
                @include('profil.bagian.hapus-akun')
            </div>
        </div>
    </div>
@endsection
