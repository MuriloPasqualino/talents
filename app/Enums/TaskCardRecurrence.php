<?php

namespace App\Enums;

enum TaskCardRecurrence: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Diariamente',
            self::Weekly => 'Semanalmente',
            self::Monthly => 'Mensalmente',
            self::Annual => 'Anualmente',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
