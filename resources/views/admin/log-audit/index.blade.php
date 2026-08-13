@extends('tata-letak.sidebar')

@section('title', 'Log Aktivitas - ' . config('app.name'))
@section('page-title', 'Log Aktivitas (Audit Trail)')

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aksi / deskripsi..." class="flex-1 rounded-lg border-gray-300">
            <button class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold sm:w-auto">Cari</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Pengguna</th>
                    <th class="px-4 py-3">Aksi</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3">
                            @if ($log->user)
                                <span class="font-semibold text-gray-800">{{ $log->user->name }}</span>
                                <div class="text-xs text-gray-400">{{ $log->user->role?->label }}</div>
                            @else
                                <span class="text-gray-400">Sistem</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"><span class="inline-block px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-mono">{{ $log->action }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada log aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
