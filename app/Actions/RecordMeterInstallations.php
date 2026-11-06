<?php

namespace App\Actions;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RecordMeterInstallations
{
    public function __invoke(
        Substation $substation,
        BalanceGroupCode $code,
        int $year,
        int $month,
        int $amount): void
    {
        $balanceGroup = BalanceGroup::where('code', $code->value)->firstOrFail();

        DB::transaction(function () use ($substation, $balanceGroup, $year, $month, $amount) {
            Substation::whereKey($substation->id)->lockForUpdate()->first();

            $stats = fn () => $substation->meterStats()->whereBelongsTo($balanceGroup);

            $selectedPeriodStat = $stats()
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if ($selectedPeriodStat) {
                $selectedPeriodStat->incrementEach([
                    'meters_total' => $amount,
                    'meters_installed' => $amount,
                ]);
            } else {
                $previousStat = $stats()
                    ->where(fn (Builder $query) => $query
                        ->where('year', '<', $year)
                        ->orWhere(fn (Builder $q) => $q->where('year', $year)->where('month', '<', $month)))
                    ->orderByDesc('year')
                    ->orderByDesc('month')
                    ->first();

                $stat = $substation->meterStats()->make([
                    'year' => $year,
                    'month' => $month,
                    'meters_total' => ($previousStat->meters_total ?? 0) + $amount,
                    'meters_installed' => $amount,
                ]);

                $stat->balanceGroup()->associate($balanceGroup);
                $stat->save();
            }

            $stats()
                ->where(fn (Builder $query) => $query
                    ->where('year', '>', $year)
                    ->orWhere(fn (Builder $q) => $q->where('year', $year)->where('month', '>', $month)))
                ->increment('meters_total', $amount);
        });
    }
}
