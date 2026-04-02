<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyTemplateQuestion extends Model
{
    protected $fillable = [
        'survey_template_section_id',
        'body',
        'reverse_score',
        'weight',
        'response_scale',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'reverse_score' => 'boolean',
            'weight' => 'float',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplateSection::class, 'survey_template_section_id');
    }
}
