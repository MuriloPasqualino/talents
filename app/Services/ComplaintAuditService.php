<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintAuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class ComplaintAuditService
{
    public static function log(Complaint $complaint, string $action, ?Request $request = null, ?User $user = null, ?array $meta = null): void
    {
        ComplaintAuditLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user?->id,
            'action' => $action,
            'meta' => $meta,
            'ip_address' => $request?->ip(),
        ]);
    }
}
