<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    protected $fillable = [
        'company_id',
        'protocol',
        'category',
        'description',
        'status',
        'is_anonymous',
        'reporter_name',
        'reporter_email',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'resolved_at' => 'datetime',
            'description' => 'encrypted',
            'reporter_name' => 'encrypted',
            'reporter_email' => 'encrypted',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ComplaintMessage::class)->orderBy('id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ComplaintAuditLog::class)->orderByDesc('id');
    }
}
