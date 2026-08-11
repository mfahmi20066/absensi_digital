<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'Arial';
            src: url('{{ str_replace('\\', '/', public_path('fonts/arial.ttf')) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Arial';
            src: url('{{ str_replace('\\', '/', public_path('fonts/arialbd.ttf')) }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'Arial';
            src: url('{{ str_replace('\\', '/', public_path('fonts/ariali.ttf')) }}') format('truetype');
            font-weight: normal;
            font-style: italic;
        }
        @font-face {
            font-family: 'Arial';
            src: url('{{ str_replace('\\', '/', public_path('fonts/arialbi.ttf')) }}') format('truetype');
            font-weight: bold;
            font-style: italic;
        }
        @page {
            size: A4 portrait;
            margin: 0;
            @bottom-center {
                content: "Halaman " counter(page) " dari " counter(pages) "  |  Sistem Absensi Digital SPPG";
                font-size: 8pt;
                color: #94a3b8;
                font-family: 'Arial', sans-serif;
            }
        }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { margin: 12mm 11mm 15mm 11mm; font-family: 'Arial', sans-serif; font-size: 9.5pt; color: #1e293b; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; }
        th, td { word-break: break-word; overflow-wrap: break-word; }
        img { max-width: 100%; }
        tr { page-break-inside: avoid; }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-logo { width: 74px; text-align: left; vertical-align: middle; }
        .kop-logo img { width: 62px; height: 62px; object-fit: contain; }
        .kop-teks { text-align: center; vertical-align: middle; }
        .kop-teks .instansi { font-size: 15pt; font-weight: bold; color: #1e3a8a; letter-spacing: 0.5px; text-transform: uppercase; }
        .kop-teks .alamat { font-size: 8pt; color: #475569; margin-top: 2px; }
        .garis { border-bottom: 3px solid #1e3a8a; margin-top: 7px; }
        .garis-bawah { border-bottom: 1px solid #1e3a8a; margin-top: 2px; }
        .meta { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .meta td { vertical-align: top; font-size: 9.5pt; padding: 1.5px 0; }
        .meta .kiri { width: 60%; }
        .meta .label { width: 62px; display: inline-block; color: #334155; }
        .meta .kanan { text-align: right; }
        .isi { margin-top: 12px; text-align: justify; font-size: 9.5pt; }
        .ringkasan { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .ringkasan td { border: 1px solid #cbd5e1; text-align: center; padding: 5px 3px; width: 16.66%; }
        .ringkasan .num { font-size: 13pt; font-weight: bold; color: #1e3a8a; }
        .ringkasan .lbl { font-size: 7.5pt; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .ringkasan .total { border: none; border-top: 2px solid #1e3a8a; text-align: right; padding-top: 4px; }
        .tabel { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .tabel thead { display: table-header-group; }
        .tabel th { background: #1e3a8a; color: #ffffff; font-size: 8pt; padding: 5px 4px; text-align: left; border: 1px solid #1e3a8a; }
        .tabel td { border: 1px solid #cbd5e1; padding: 4px 4px; font-size: 8pt; }
        .tabel tr:nth-child(even) td { background: #f1f5f9; }
        .tabel .no { text-align: center; width: 4%; }
        .tabel .nip { width: 14%; font-family: 'Courier New', monospace; font-size: 7pt; }
        .tabel .nama { width: 23%; }
        .tabel .jabatan { width: 20%; }
        .tabel .tanggal { width: 12%; }
        .tabel .jam { width: 9%; }
        .tabel .status { width: 9%; }
        .penutup { margin-top: 12px; text-align: justify; font-size: 9.5pt; }
        .ttd { width: 100%; margin-top: 30px; }
        .ttd td { vertical-align: top; font-size: 9.5pt; }
        .ttd .kanan { width: 44%; text-align: center; }
        .ttd .jabatan { margin-top: 4px; }
        .ttd .blok { margin-top: 64px; }
        .ttd .garis-nama { border-bottom: 1px solid #1e293b; width: 74%; margin: 0 auto 2px; font-weight: bold; }
    </style>
</head>
<body>
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <img src="{{ ($printMode ?? false) ? asset('images/logos/sppg-logo.png') : public_path('images/logos/sppg-logo.png') }}" alt="Logo">
            </td>
            <td class="kop-teks">
                <div class="instansi">{{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</div>
                <div class="alamat">{{ \App\Models\Pengaturan::get('sppg_address', '') }}</div>
            </td>
        </tr>
    </table>
    <div class="garis"></div>
    <div class="garis-bawah"></div>

    <table class="meta">
        <tr>
            <td class="kiri">
                <div><span class="label">Nomor</span>: {{ $nomorSurat }}</div>
                <div><span class="label">Lampiran</span>: -</div>
                <div><span class="label">Perihal</span>: <strong>Laporan Absensi Karyawan Periode {{ $header }}</strong></div>
            </td>
            <td class="kanan">
                <div>{{ $tanggalSurat }}</div>
            </td>
        </tr>
    </table>

    <div class="isi">
        <div style="margin-top:8px;">Dengan hormat,</div>
        <p style="margin-top:6px; text-indent: 2em;">
            Bersama ini kami sampaikan Laporan Absensi Karyawan {{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }} untuk periode
            <strong>{{ $header }}</strong>. Adapun rekapitulasi kehadiran karyawan pada periode tersebut adalah sebagai berikut:
        </p>
    </div>

    <table class="ringkasan">
        <tr>
            <td><div class="num">{{ $summary->get('hadir', 0) }}</div><div class="lbl">Hadir</div></td>
            <td><div class="num">{{ $summary->get('telat', 0) }}</div><div class="lbl">Telat</div></td>
            <td><div class="num">{{ $summary->get('izin', 0) }}</div><div class="lbl">Izin</div></td>
            <td><div class="num">{{ $summary->get('sakit', 0) }}</div><div class="lbl">Sakit</div></td>
            <td><div class="num">{{ $summary->get('cuti', 0) }}</div><div class="lbl">Cuti</div></td>
            <td><div class="num">{{ $summary->get('alpha', 0) }}</div><div class="lbl">Alpha</div></td>
        </tr>
        <tr>
            <td class="total" colspan="6"><strong>Total Catatan: {{ $attendances->count() }}</strong></td>
        </tr>
    </table>

    <table class="tabel">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="nip">NIP</th>
                <th class="nama">Nama</th>
                <th class="jabatan">Jabatan</th>
                <th class="tanggal">Tanggal</th>
                <th class="jam">Masuk</th>
                <th class="jam">Pulang</th>
                <th class="status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $i => $att)
                <tr>
                    <td class="no">{{ $i + 1 }}</td>
                    <td class="nip">{{ $att->employee->nip }}</td>
                    <td class="nama"><strong>{{ $att->employee->user->name }}</strong></td>
                    <td class="jabatan">{{ $att->employee->position }}</td>
                    <td class="tanggal">{{ $att->date->format('d/m/Y') }}</td>
                    <td class="jam">{{ $att->time_in?->format('H:i') ?? '-' }}</td>
                    <td class="jam">{{ $att->time_out?->format('H:i') ?? '-' }}</td>
                    <td class="status"><strong>{{ $att->statusLabel }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:14px; color:#94a3b8;">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="penutup">
        Demikian laporan ini kami sampaikan sebagai bahan informasi dan evaluasi kehadiran karyawan. Atas perhatian dan kerja samanya kami ucapkan terima kasih.
    </p>

    <table class="ttd">
        <tr>
            <td style="width:56%;"></td>
            <td class="kanan">
                <div>Mengetahui,</div>
                <div class="jabatan">Kepala {{ \App\Models\Pengaturan::get('sppg_name', 'SPPG') }}</div>
                <div class="blok">
                    <div class="garis-nama">{{ $kepalaNama ?? '........................................' }}</div>
                    <div style="margin-top:2px;">NIP. {{ $kepalaNip ?? '........................................' }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if (($printMode ?? false))
        <script>window.print();</script>
    @endif
</body>
</html>
