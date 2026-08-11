@extends('tata-letak.sidebar')

@section('title', 'Barcode Saya - ' . config('app.name'))
@section('page-title', 'Barcode Saya')

@section('content')
    <div class="max-w-md mx-auto space-y-5">
        <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
            <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Kartu Barcode Absensi</div>
            <h2 class="mt-1 font-bold text-gray-800">{{ auth()->user()->name }}</h2>
            <div class="text-sm text-gray-500">{{ $employee->nip }} &middot; {{ $employee->position }}</div>

            <div class="mt-5 bg-white border-2 border-blue-900 rounded-2xl p-5 inline-block">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(240)
                    ->margin(1)
                    ->errorCorrection('M')
                    ->generate(route('absen.scan.show', $barcode->code)) !!}
            </div>

            <div class="mt-4 text-xs text-gray-400">
                Kode: <span class="font-mono font-semibold text-gray-600">{{ $barcode->code }}</span><br>
                Berlaku: {{ $barcode->valid_from->format('d/m/Y') }} s/d {{ $barcode->valid_until?->format('d/m/Y') ?? 'selamanya' }}
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-900 flex items-start gap-2">
            <x-ikon name="lightbulb" class="w-4 h-4 shrink-0 mt-0.5" /> <span>Tunjukkan barcode ini ke kamera perangkat absen, atau simpan foto layar ini di HP Anda. Anda juga bisa mencetaknya sebagai kartu.</span>
        </div>

        <div class="flex gap-3">
            <button onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-gray-800 text-white font-semibold text-sm inline-flex items-center justify-center gap-2"><x-ikon name="printer" class="w-4 h-4" /> Cetak Kartu</button>
            <button onclick="shareQr()" class="flex-1 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold text-sm inline-flex items-center justify-center gap-2" id="share-btn"><x-ikon name="share" class="w-4 h-4" /> Simpan / Bagikan</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        async function shareQr() {
            const card = document.querySelector('.bg-white.border-2');
            const btn = document.getElementById('share-btn');
            try {
                const canvas = await html2canvas(card);
                const blob = await new Promise(r => canvas.toBlob(r, 'image/png'));
                const file = new File([blob], 'barcode-saya.png', { type: 'image/png' });
                if (navigator.share && navigator.canShare({ files: [file] })) {
                    await navigator.share({ files: [file], title: 'Barcode Absensi Saya' });
                } else {
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'barcode-saya.png';
                    a.click();
                }
            } catch (e) {
                btn.textContent = 'Gagal - gunakan tombol Cetak';
                setTimeout(() => btn.textContent = 'Simpan / Bagikan', 2500);
            }
        }
    </script>
@endpush
