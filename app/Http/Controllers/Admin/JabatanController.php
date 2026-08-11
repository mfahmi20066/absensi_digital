<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Support\PencatatAudit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::withCount('karyawan')->orderBy('name')->get();

        return view('admin.jabatan.index', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')],
        ]);

        $jabatan = Jabatan::create($data);

        PencatatAudit::log('position_created', "Jabatan {$jabatan->name} ditambahkan");

        return redirect()->route('admin.jabatan.index')->with('success', "Jabatan {$jabatan->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($jabatan->id)],
        ]);

        $namaLama = $jabatan->name;
        $jabatan->update($data);

        Karyawan::where('position_id', $jabatan->id)->update(['position' => $jabatan->name]);

        PencatatAudit::log('position_updated', "Jabatan {$namaLama} diubah menjadi {$jabatan->name}");

        return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Jabatan $jabatan)
    {
        if ($jabatan->karyawan()->exists()) {
            return redirect()->route('admin.jabatan.index')->with('error', "Jabatan {$jabatan->name} masih digunakan oleh karyawan, tidak dapat dihapus.");
        }

        $nama = $jabatan->name;
        $jabatan->delete();

        PencatatAudit::log('position_deleted', "Jabatan {$nama} dihapus");

        return redirect()->route('admin.jabatan.index')->with('success', "Jabatan {$nama} berhasil dihapus.");
    }
}
