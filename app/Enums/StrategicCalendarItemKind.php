<?php

namespace App\Enums;

enum StrategicCalendarItemKind: string
{
    case Event = 'event';
    case Rito = 'rito';

    public function label(): string
    {
        return match ($this) {
            self::Event => 'Evento',
            self::Rito => 'Rito',
        };
    }
}
