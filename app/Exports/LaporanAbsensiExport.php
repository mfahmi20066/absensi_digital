<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanAbsensiExport
{
    private const STATUS = [
        'hadir' => 'Hadir',
        'telat' => 'Telat',
        'izin' => 'Izin',
        'sakit' => 'Sakit',
        'cuti' => 'Cuti',
        'alpha' => 'Alpha',
    ];

    private const COLUMNS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

    public function __construct(
        private string $period,
        private string $title,
        private Collection $attendances,
        private Collection $summary,
    ) {}

    public function filename(): string
    {
        return 'laporan-absensi-' . $this->period . '-' . now()->format('Ymd-His') . '.xlsx';
    }

    public function download()
    {
        $spreadsheet = $this->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $this->filename(), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function save(string $path): void
    {
        (new Xlsx($this->build()))->save($path);
    }

    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Absensi');

        $last = self::COLUMNS[count(self::COLUMNS) - 1];

        $sheet->setCellValue('A1', $this->title);
        $sheet->mergeCells('A1:' . $last . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('1E3A8A');

        $sheet->setCellValue('A2', 'Dicetak: ' . now()->translatedFormat('d F Y H:i'));
        $sheet->mergeCells('A2:' . $last . '2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setARGB('64748B');

        $ringkasan = collect(self::STATUS)
            ->map(fn (string $label, string $key) => $label . ': ' . $this->summary->get($key, 0))
            ->implode('   |   ');

        $sheet->setCellValue('A3', $ringkasan);
        $sheet->mergeCells('A3:' . $last . '3');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2E8F0');

        $headers = ['No', 'NIP', 'Nama', 'Jabatan', 'Shift', 'Tanggal', 'Masuk', 'Pulang', 'Metode Masuk', 'Metode Pulang', 'Status', 'Catatan'];

        $headerRow = 5;
        foreach ($headers as $i => $header) {
            $cell = self::COLUMNS[$i] . $headerRow;
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1E3A8A');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $row = $headerRow + 1;
        foreach ($this->attendances as $i => $att) {
            $values = [
                (string) ($i + 1),
                $att->employee->nip,
                $att->employee->user->name,
                $att->employee->position,
                $att->workSchedule?->name ?? '-',
                $att->date->format('d/m/Y'),
                $att->time_in?->format('H:i') ?? '-',
                $att->time_out?->format('H:i') ?? '-',
                $att->method_in ?? '-',
                $att->method_out ?? '-',
                $att->statusLabel,
                $att->notes ?? '',
            ];

            foreach ($values as $i => $value) {
                $cell = self::COLUMNS[$i] . $row;
                $isNumeric = in_array(self::COLUMNS[$i], ['B', 'F', 'G', 'H']);
                if ($isNumeric) {
                    $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($cell, $value);
                }
                $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                if (in_array(self::COLUMNS[$i], ['A', 'F', 'G', 'H', 'I', 'J', 'K'])) {
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }
            $sheet->getStyle('L' . $row)->getAlignment()->setWrapText(true);
            $row++;
        }

        $sheet->setCellValue('A' . ($row + 1), 'Total Catatan: ' . $this->attendances->count());
        $sheet->mergeCells('A' . ($row + 1) . ':' . $last . ($row + 1));
        $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);
        $sheet->getStyle('A' . ($row + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        foreach (self::COLUMNS as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A' . ($headerRow + 1));

        return $spreadsheet;
    }
}
