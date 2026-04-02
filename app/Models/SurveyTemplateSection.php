<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyTemplateSection extends Model
{
    protected $fillable = [
        'survey_template_id',
        'title',
        'description',
        'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyTemplateQuestion::class)->orderBy('sort_order');
    }
}
