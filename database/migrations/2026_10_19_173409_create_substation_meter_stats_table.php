<?php

use App\Models\BalanceGroup;
use App\Models\Substation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('substation_meter_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Substation::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(BalanceGroup::class)->constrained();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('meters_total')->default(0);
            $table->unsignedInteger('meters_installed')->default(0);
            $table->timestamps();

            $table->unique(
                ['substation_id', 'balance_group_id', 'year', 'month'],
                'meter_stats_period_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substation_meter_stats');
    }
};
