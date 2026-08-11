@extends('tata-letak.sidebar')

@section('title', 'Kelola Pengguna - ' . config('app.name'))
@section('page-title', 'Kelola Pengguna')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <div class="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..." class="flex-1 rounded-lg border-gray-300">
                <select name="role" class="rounded-lg border-gray-300">
                    <option value="all">Semua Peran</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(request('role') === (string) $role->id)>{{ $role->label }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border-gray-300">
                    <option value="all">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
                <button class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold">Cari</button>
            </form>
            <a href="{{ route('admin.pengguna.create') }}" class="px-4 py-2 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold text-center whitespace-nowrap inline-flex items-center gap-1.5"><x-ikon name="plus" class="w-4 h-4" /> Tambah Pengguna</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Pengguna</th>
                    <th class="px-4 py-3">Peran</th>
                    <th class="px-4 py-3">Verifikasi Email</th>
                    <th class="px-4 py-3">Data Karyawan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                <div>
                                    <div class="font-semibold text-gray-800">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="ml-1 text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">Anda</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->role_id === 1 ? 'bg-purple-100 text-purple-700' : ($user->role_id === 2 ? 'bg-sky-100 text-sky-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $user->role?->label ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->email_verified_at)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600"><x-ikon name="check-circle" class="w-4 h-4" /> Terverifikasi</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600"><x-ikon name="clock" class="w-4 h-4" /> Belum</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if ($user->employee)
                                <a href="{{ route('admin.karyawan.edit', $user->employee) }}" class="text-blue-700 hover:underline">NIP: {{ $user->employee->nip ?? '-' }}</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.pengguna.edit', $user) }}" title="Edit" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500"><x-ikon name="pencil" class="w-4 h-4" /></a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.pengguna.destroy', $user) }}" onsubmit="return confirmSubmit(this, 'Hapus pengguna {{ $user->name }}? Akun ini akan dihapus permanen.', 'Ya, hapus')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-400" title="Hapus"><x-ikon name="trash" class="w-4 h-4" /></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
