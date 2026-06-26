<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StrategicCalendarItemAttachment extends Model
{
    protected $fillable = [
        'strategic_calendar_item_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'uploaded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(StrategicCalendarItem::class, 'strategic_calendar_item_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function downloadName(): string
    {
        return $this->original_name ?: 'anexo';
    }

    public function isVideo(): bool
    {
        if (is_string($this->mime)) {
            $mime = strtolower($this->mime);
            if (str_starts_with($mime, 'video/')) {
                return true;
            }
            if (in_array($mime, ['application/mp4', 'application/x-mp4'], true)) {
                return true;
            }
        }

        $ext = strtolower(pathinfo($this->original_name ?? '', PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'mkv', 'm4v'], true);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    public function toHttpResponse(): StreamedResponse|Response
    {
        if (! Storage::disk($this->disk)->exists($this->path)) {
            abort(404);
        }

        if ($this->isVideo()) {
            $filename = str_replace('"', '', $this->downloadName());
            $contentType = is_string($this->mime) && str_starts_with(strtolower($this->mime), 'video/')
                ? $this->mime
                : 'video/mp4';

            return Storage::disk($this->disk)->response(
                $this->path,
                $this->downloadName(),
                [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'inline; filename="'.$filename.'"',
                ],
            );
        }

        return Storage::disk($this->disk)->download(
            $this->path,
            $this->downloadName(),
        );
    }

    public function deleteStoredFile(): void
    {
        if ($this->path && $this->disk) {
            $disk = Storage::disk($this->disk);
            if ($disk->exists($this->path)) {
                $disk->delete($this->path);
            }
        }
    }
}
