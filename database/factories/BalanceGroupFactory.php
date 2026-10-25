<?php

namespace Database\Factories;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BalanceGroup>
 */
class BalanceGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        throw new \LogicException(
            'BalanceGroup — канонический справочник. Используйте household(), legalUspd(), legalGsm(), odpuUspd() или odpuGsm().'
        );
    }

    public function household(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::Household->value,
            'name' => BalanceGroupCode::Household->label(),
        ]);
    }

    public function legalUspd(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::LegalUspd->value,
            'name' => BalanceGroupCode::LegalUspd->label(),
        ]);
    }

    public function legalGsm(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::LegalGsm->value,
            'name' => BalanceGroupCode::LegalGsm->label(),
        ]);
    }

    public function odpuUspd(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::OdpuUspd->value,
            'name' => BalanceGroupCode::OdpuUspd->label(),
        ]);
    }

    public function odpuGsm(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::OdpuGsm->value,
            'name' => BalanceGroupCode::OdpuGsm->label(),
        ]);
    }
}
