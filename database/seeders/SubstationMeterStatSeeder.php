<?php

namespace Database\Seeders;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use App\Models\SubstationMeterStat;
use Illuminate\Database\Seeder;

class SubstationMeterStatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = BalanceGroupCode::cases();

        $groupIds = BalanceGroup::whereIn('code', array_column($codes, 'value'))
            ->pluck('id', 'code');

        foreach (Substation::all() as $substation) {
            foreach (range(1, 6) as $monthsAgo) {
                $date = now()->startOfMonth()->subMonths($monthsAgo);

                foreach ($codes as $code) {
                    $total = fake()->numberBetween(10, 100);

                    SubstationMeterStat::firstOrCreate(
                        [
                            'substation_id' => $substation->id,
                            'balance_group_id' => $groupIds[$code->value],
                            'year' => (int) $date->year,
                            'month' => (int) $date->month,
                        ],
                        [
                            'meters_total' => $total,
                            'meters_installed' => fake()->numberBetween(0, $total),
                        ],
                    );
                }
            }
        }
    }
}
