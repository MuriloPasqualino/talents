<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyTemplate extends Model
{
    protected $fillable = ['title', 'description', 'is_active', 'created_by'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(SurveyTemplateSection::class)->orderBy('sort_order');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_survey_template')
            ->withTimestamps();
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
