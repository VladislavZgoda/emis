<?php

namespace Database\Seeders;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use Illuminate\Database\Seeder;

class BalanceGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (BalanceGroupCode::cases() as $code) {
            BalanceGroup::updateOrCreate(
                ['code' => $code->value],
                ['name' => $code->label()],
            );
        }
    }
}
