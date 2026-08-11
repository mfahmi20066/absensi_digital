<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = Pengaturan::query()->orderBy('key')->get()->pluck('value', 'key');

        return view('admin.pengaturan.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'sppg_name' => ['sometimes', 'required', 'string', 'max:255'],
            'sppg_address' => ['sometimes', 'required', 'string', 'max:500'],
            'sppg_latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'sppg_longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['sometimes', 'required', 'integer', 'min:10', 'max:10000'],
            'default_tolerance_minutes' => ['sometimes', 'required', 'integer', 'min:0', 'max:180'],
            'leave_quota' => ['sometimes', 'required', 'integer', 'min:0', 'max:365'],
            'kepala_nama' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kepala_nip' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        foreach ($data as $key => $value) {
            Pengaturan::set($key, $value);
        }

        PencatatAudit::log('settings_updated', 'Pengaturan sistem diperbarui');

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
