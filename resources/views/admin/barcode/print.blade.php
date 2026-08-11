<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Kartu Barcode - {{ $employee->user->name }}</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: Arial, sans-serif; display: flex; justify-content: center; padding: 20px; }
            .card {
                width: 85mm; height: 54mm; border: 2px solid #107a57; border-radius: 8px;
                display: flex; align-items: center; padding: 5mm; gap: 4mm; background: #fff;
            }
            .logo { width: 22mm; height: 22mm; object-fit: contain; }
            .left { text-align: center; }
            .info { flex: 1; }
            .sppg { font-size: 9px; color: #107a57; font-weight: bold; }
            .name { font-size: 12px; font-weight: bold; margin-top: 1mm; }
            .nip { font-size: 9px; color: #555; }
            .pos { font-size: 9px; color: #555; }
            .qr { margin-top: 1mm; text-align: center; }
            .qr svg { width: 24mm; height: 24mm; }
            .code { font-size: 8px; letter-spacing: 1px; color: #444; margin-top: 0.5mm; }
            @media print {
                body { padding: 0; }
                .card { page-break-after: always; }
                @page { size: 85mm 54mm; margin: 0; }
            }
        </style>
    </head>
    <body>
        @php
            $qrUrl = route('absen.scan.show', $barcode->code);
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->margin(1)->errorCorrection('M')->generate($qrUrl);
        @endphp
        <div class="card">
            <div class="left">
                <img src="{{ asset('images/logos/sppg-logo.png') }}" alt="Logo" class="logo">
            </div>
            <div class="info">
                <div class="sppg">{{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</div>
                <div class="name">{{ $employee->user->name }}</div>
                <div class="nip">{{ $employee->nip }}</div>
                <div class="pos">{{ $employee->position }}</div>
                <div class="qr">{!! $qrSvg !!}</div>
                <div class="code">{{ $barcode->code }}</div>
            </div>
        </div>
        <script>window.print();</script>
    </body>
</html>
