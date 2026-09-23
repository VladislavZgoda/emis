<?php

namespace Database\Factories;

use App\Models\Substation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Substation>
 */
class SubstationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = $this->faker->unique()->numberBetween(1, 999);
        $suffix = $this->faker->boolean(30) ? 'П' : '';

        return [
            'name' => "ТП-{$number}{$suffix}",
        ];
    }
}
