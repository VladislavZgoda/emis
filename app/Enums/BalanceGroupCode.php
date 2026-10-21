<?php

namespace App\Enums;

enum BalanceGroupCode: string
{
    case Household = 'household';
    case Legal = 'legal';
    case Odpu = 'odpu';

    public function label(): string
    {
        return match ($this) {
            self::Household => 'БЫТ',
            self::Legal => 'Юридические лица',
            self::Odpu => 'ОДПУ',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }
}
