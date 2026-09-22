<?php

namespace Database\Seeders;

use App\Models\Substation;
use Illuminate\Database\Seeder;

class SubstationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $count = 100): void
    {
        Substation::factory()->count($count)->create();
    }
}
