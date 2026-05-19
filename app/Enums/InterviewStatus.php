<?php

namespace App\Enums;

enum InterviewStatus: string
{
    case Queued = 'queued';
    case Transcribing = 'transcribing';
    case Extracting = 'extracting';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Na fila',
            self::Transcribing => 'Transcrevendo áudio',
            self::Extracting => 'Gerando relatório',
            self::Completed => 'Concluída',
            self::Failed => 'Falhou',
        };
    }

    public function isProcessing(): bool
    {
        return in_array($this, [self::Queued, self::Transcribing, self::Extracting], true);
    }
}
