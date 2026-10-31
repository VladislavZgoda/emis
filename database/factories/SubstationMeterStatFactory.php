<?php

namespace Database\Factories;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use App\Models\SubstationMeterStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubstationMeterStat>
 */
class SubstationMeterStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'substation_id' => Substation::factory(),
            'balance_group_id' => fn () => self::groupId(BalanceGroupCode::Household),
            'year' => now()->year,
            'month' => now()->month,
            'meters_total' => fake()->numberBetween(10, 100),
            'meters_installed' => fn (array $attrs) => fake()->numberBetween(0, $attrs['meters_total']),
        ];
    }

    public function forPeriod(int $year, int $month): static
    {
        return $this->state(fn () => ['year' => $year, 'month' => $month]);
    }

    public function forGroup(BalanceGroupCode $code): static
    {
        return $this->state(['balance_group_id' => fn () => self::groupId($code)]);
    }

    public function fullyInstalled(): static
    {
        return $this->state(['meters_installed' => fn (array $attrs) => $attrs['meters_total']]);
    }

    public function noneInstalled(): static
    {
        return $this->state(['meters_installed' => 0]);
    }

    private static function groupId(BalanceGroupCode $code): int
    {
        return BalanceGroup::firstOrCreate(
            ['code' => $code->value],
            ['name' => $code->label()],
        )->id;
    }
}
