<?php

namespace App\Models;

use App\Enums\StrategicCalendarItemKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategicCalendarItem extends Model
{
    protected $fillable = [
        'title',
        'description',
        'kind',
        'occurs_on',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'occurs_on' => 'date',
            'kind' => StrategicCalendarItemKind::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Itens visíveis para a empresa: globais (company_id null) ou específicos da empresa.
     */
    public function scopeForCompany(Builder $query, Company $company): Builder
    {
        return $query->where(function (Builder $q) use ($company) {
            $q->whereNull('company_id')
                ->orWhere('company_id', $company->id);
        });
    }
}
