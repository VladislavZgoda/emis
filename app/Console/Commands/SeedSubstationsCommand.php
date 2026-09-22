<?php

namespace App\Console\Commands;

use Database\Seeders\SubstationSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seed:substations {count=100 : Количество подстанций для генерации}')]
#[Description('Сгенерировать тестовые подстанции с указанным количеством')]
class SeedSubstationsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SubstationSeeder $seeder): void
    {
        $count = (int) $this->argument('count');

        $seeder->run($count);

        $this->info("Создано {$count} подстанций.");
    }
}
