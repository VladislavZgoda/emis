<?php

namespace Database\Seeders;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use App\Models\SubstationMeterStat;
use Illuminate\Database\Seeder;

class SubstationMeterStatSeeder extends Seeder
{
    public function run(): void
    {
        $codes = BalanceGroupCode::cases();

        $groupIds = BalanceGroup::whereIn('code', array_column($codes, 'value'))
            ->pluck('id', 'code');

        $months = collect(range(6, 1))
            ->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));

        Substation::query()->each(function (Substation $substation) use ($codes, $groupIds, $months) {
            $rows = [];

            foreach ($codes as $code) {
                $total = fake()->numberBetween(10, 100);

                foreach ($months as $date) {
                    $installed = fake()->numberBetween(0, 10);
                    $total += $installed;

                    $rows[] = [
                        'substation_id' => $substation->id,
                        'balance_group_id' => $groupIds[$code->value],
                        'year' => $date->year,
                        'month' => $date->month,
                        'meters_total' => $total,
                        'meters_installed' => $installed,
                    ];
                }
            }

            SubstationMeterStat::upsert(
                $rows,
                ['substation_id', 'balance_group_id', 'year', 'month'],
                ['meters_total', 'meters_installed'],
            );
        });
    }
}
