<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewQuestionnaireSection extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'title',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(InterviewQuestionnaire::class, 'questionnaire_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(InterviewQuestionnaireQuestion::class, 'section_id')->orderBy('position');
    }
}
