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
            'BalanceGroup — канонический справочник. Используйте household(), legal() или odpu().'
        );
    }

    public function household(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::Household->value,
            'name' => BalanceGroupCode::Household->label(),
        ]);
    }

    public function legal(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::Legal->value,
            'name' => BalanceGroupCode::Legal->label(),
        ]);
    }

    public function odpu(): static
    {
        return $this->state([
            'code' => BalanceGroupCode::Odpu->value,
            'name' => BalanceGroupCode::Odpu->label(),
        ]);
    }
}
