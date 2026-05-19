<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewQuestionnaire extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_default',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(InterviewQuestionnaireSection::class, 'questionnaire_id')->orderBy('position');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'questionnaire_id');
    }

    public static function defaultQuestionnaire(): ?self
    {
        return static::query()->where('is_default', true)->first()
            ?? static::query()->orderBy('id')->first();
    }
}
