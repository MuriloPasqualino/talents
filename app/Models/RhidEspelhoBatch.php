<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RhidEspelhoBatch extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'status',
        'total',
        'processed',
        'succeeded',
        'skipped',
        'current_id_person',
        'skipped_person_ids',
        'meta_json',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'processed' => 'integer',
            'succeeded' => 'integer',
            'skipped' => 'integer',
            'current_id_person' => 'integer',
            'skipped_person_ids' => 'array',
            'meta_json' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
