<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barcode;
use App\Models\Karyawan;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::with('user', 'workSchedule', 'activeBarcode');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nip', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$request->search}%"));
            });
        }

        $employees = $query->where('status', 'aktif')->paginate(15)->withQueryString();

        return view('admin.barcode.index', compact('employees'));
    }

    public function generate(Request $request, Karyawan $employee)
    {
        $request->validate([
            'valid_until' => ['nullable', 'date'],
        ]);

        $employee->barcodes()->update(['is_active' => false]);

        Barcode::create([
            'employee_id' => $employee->id,
            'code' => strtoupper($employee->nip . '-' . now()->format('Ymd') . '-' . random_int(1000, 9999)),
            'valid_from' => today(),
            'valid_until' => $request->valid_until ?? today()->addYear(),
            'is_active' => true,
        ]);

        PencatatAudit::log('barcode_generated', "Barcode baru untuk {$employee->nip} - {$employee->user->name}");

        return back()->with('success', 'Barcode baru berhasil dibuat. Barcode lama otomatis nonaktif.');
    }

    public function regenerate(Request $request, Karyawan $employee)
    {
        return $this->generate($request, $employee);
    }

    public function print(Karyawan $employee)
    {
        $barcode = $employee->activeBarcode;

        abort_unless($barcode, 404, 'Barcode aktif tidak ditemukan.');

        $qrSvg = QrCode::format('svg')
            ->size(220)
            ->margin(1)
            ->errorCorrection('M')
            ->generate(route('absen.scan.show', $barcode->code));

        return view('admin.barcode.print', compact('employee', 'barcode', 'qrSvg'));
    }

    public function downloadPng(Karyawan $employee)
    {
        $barcode = $employee->activeBarcode;

        abort_unless($barcode, 404, 'Barcode aktif tidak ditemukan.');

        $url = route('absen.scan.show', $barcode->code);

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(600, 4),
            new \App\Support\GdImageBackEnd()
        );

        $png = $renderer->render(\BaconQrCode\Encoder\Encoder::encode(
            $url,
            \BaconQrCode\Common\ErrorCorrectionLevel::M()
        ));

        return response($png, 200, ['Content-Type' => 'image/png'])
            ->header('Content-Disposition', "attachment; filename=barcode-{$employee->nip}.png");
    }
}
