@extends('tata-letak.sidebar')

@section('title', 'Edit Absensi - ' . config('app.name'))
@section('page-title', 'Edit Absensi')

@section('content')
    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-800">{{ $attendance->employee?->user?->name ?? '-' }}</h3>
                    <div class="text-sm text-gray-500">{{ $attendance->employee?->nip ?? '-' }} &middot; {{ $attendance->date->translatedFormat('l, d F Y') }}</div>
                </div>
                <span class="inline-block px-3 py-1 rounded-full text-sm font-bold
                    {{ $attendance->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($attendance->status === 'telat' ? 'bg-amber-100 text-amber-700' : ($attendance->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-700')) }}">
                    {{ $attendance->statusLabel }}
                </span>
            </div>

            @if ($attendance->photo_in || $attendance->photo_out)
                <div class="grid grid-cols-2 gap-4 mb-5">
                    @if ($attendance->photo_in)
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $attendance->photo_in) }}" alt="Foto masuk" class="w-40 h-40 object-cover rounded-xl mx-auto">
                            <div class="text-xs text-gray-400 mt-1">Foto masuk ({{ $attendance->time_in?->format('H:i') }})</div>
                        </div>
                    @endif
                    @if ($attendance->photo_out)
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $attendance->photo_out) }}" alt="Foto pulang" class="w-40 h-40 object-cover rounded-xl mx-auto">
                            <div class="text-xs text-gray-400 mt-1">Foto pulang ({{ $attendance->time_out?->format('H:i') }})</div>
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.absensi.update', $attendance) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Jam Masuk</label>
                        <input type="time" name="time_in" value="{{ $attendance->time_in?->format('H:i') }}" class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500">Jam Pulang</label>
                        <input type="time" name="time_out" value="{{ $attendance->time_out?->format('H:i') }}" class="mt-1 w-full rounded-lg border-gray-300">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Status *</label>
                    <select name="status" class="mt-1 w-full rounded-lg border-gray-300">
                        @foreach (['hadir', 'telat', 'izin', 'sakit', 'cuti', 'alpha'] as $st)
                            <option value="{{ $st }}" @selected($attendance->status === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Catatan</label>
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-gray-300">{{ $attendance->notes }}</textarea>
                </div>
                <div class="flex gap-3">
                    <button class="flex-1 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold">Simpan</button>
                    <a href="{{ route('admin.absensi.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
