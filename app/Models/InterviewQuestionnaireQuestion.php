<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewQuestionnaireQuestion extends Model
{
    protected $fillable = [
        'section_id',
        'question_key',
        'text',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(InterviewQuestionnaireSection::class, 'section_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InterviewAnswer::class, 'question_id');
    }
}
