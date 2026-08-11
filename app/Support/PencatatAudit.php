<?php

namespace App\Support;

use App\Models\LogAudit;
use Illuminate\Support\Facades\Auth;

class PencatatAudit
{
    public static function log(string $action, string $description = ''): void
    {
        LogAudit::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
