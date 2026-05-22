<?php

namespace App\Models;

use App\Enums\StrategicCalendarItemKind;
use App\Enums\StrategicCalendarRecurrence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StrategicCalendarItem extends Model
{
    protected $fillable = [
        'title',
        'description',
        'kind',
        'occurs_on',
        'recurrence',
        'recurrence_ends_on',
        'company_id',
        'attachment_disk',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'attachment_size',
    ];

    protected function casts(): array
    {
        return [
            'occurs_on' => 'date',
            'recurrence_ends_on' => 'date',
            'kind' => StrategicCalendarItemKind::class,
            'recurrence' => StrategicCalendarRecurrence::class,
            'attachment_size' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function hasAttachment(): bool
    {
        return $this->attachment_path !== null
            && $this->attachment_disk !== null
            && Storage::disk($this->attachment_disk)->exists($this->attachment_path);
    }

    public function deleteAttachmentFile(): void
    {
        if ($this->attachment_path && $this->attachment_disk) {
            $disk = Storage::disk($this->attachment_disk);
            if ($disk->exists($this->attachment_path)) {
                $disk->delete($this->attachment_path);
            }
        }
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
