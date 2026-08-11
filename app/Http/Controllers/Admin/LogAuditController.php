<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAudit;
use Illuminate\Http\Request;

class LogAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = LogAudit::with('user');

        if ($request->filled('search')) {
            $query->where('action', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }

        $logs = $query->latest()->paginate(30)->withQueryString();

        return view('admin.log-audit.index', compact('logs'));
    }
}
