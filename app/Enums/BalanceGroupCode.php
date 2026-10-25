<?php

namespace App\Enums;

enum BalanceGroupCode: string
{
    case Household = 'household';
    case LegalUspd = 'legal_uspd';
    case LegalGsm = 'legal_gsm';
    case OdpuUspd = 'odpu_uspd';
    case OdpuGsm = 'odpu_gsm';

    public function label(): string
    {
        return match ($this) {
            self::Household => 'БЫТ',
            self::LegalUspd => 'Юридические лица (УСПД)',
            self::LegalGsm => 'Юридические лица (GSM-модем)',
            self::OdpuUspd => 'ОДПУ (УСПД)',
            self::OdpuGsm => 'ОДПУ (GSM-модем)',
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
