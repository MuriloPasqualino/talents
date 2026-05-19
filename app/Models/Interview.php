<?php

namespace App\Models;

use App\Enums\InterviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Interview extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'candidate_name',
        'position_title',
        'company_id',
        'status',
        'audio_path',
        'audio_mime',
        'audio_size',
        'duration_seconds',
        'transcript_text',
        'failure_reason',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => InterviewStatus::class,
            'audio_size' => 'integer',
            'duration_seconds' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(InterviewQuestionnaire::class, 'questionnaire_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InterviewAnswer::class);
    }

    public function audioAbsolutePath(): ?string
    {
        if (! $this->audio_path) {
            return null;
        }

        return Storage::disk('local')->path($this->audio_path);
    }

    public function markProcessing(InterviewStatus $status): void
    {
        $this->update([
            'status' => $status,
            'failure_reason' => null,
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => InterviewStatus::Completed,
            'finished_at' => now(),
            'failure_reason' => null,
        ]);
    }

    public function markFailed(string $reason): void
    {
        $this->update([
            'status' => InterviewStatus::Failed,
            'failure_reason' => $reason,
            'finished_at' => now(),
        ]);
    }
}
