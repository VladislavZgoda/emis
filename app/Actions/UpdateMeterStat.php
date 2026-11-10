<?php

namespace App\Actions;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateMeterStat
{
    public function __invoke(
        Substation $substation,
        BalanceGroupCode $code,
        int $year,
        int $month,
        int $metersTotal,
        int $metersInstalled,
    ): void {
        $balanceGroup = BalanceGroup::where('code', $code->value)->firstOrFail();

        DB::transaction(function () use ($substation, $balanceGroup, $year, $month, $metersTotal, $metersInstalled) {
            Substation::whereKey($substation->id)
                ->lockForUpdate()
                ->first();

            $stats = fn () => $substation
                ->meterStats()
                ->whereBelongsTo($balanceGroup);

            $laterStats = fn () => $stats()
                ->where(fn (Builder $query) => $query
                    ->where('year', '>', $year)
                    ->orWhere(fn (Builder $q) => $q->where('year', $year)
                        ->where('month', '>', $month)));

            $selectedPeriodStat = $stats()
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            // Базовое значение для расчёта сдвига: своё текущее или последнее из предыдущих периодов
            $previousTotal = $selectedPeriodStat->meters_total
                ?? $stats()
                    ->where(fn (Builder $query) => $query
                        ->where('year', '<', $year)
                        ->orWhere(fn (Builder $q) => $q->where('year', $year)->where('month', '<', $month)))
                    ->orderByDesc('year')
                    ->orderByDesc('month')
                    ->value('meters_total')
                ?? 0;

            $diffTotal = $metersTotal - $previousTotal;

            if ($diffTotal < 0) {
                $shift = abs($diffTotal);

                $conflict = $laterStats()
                    ->get(['meters_total', 'meters_installed'])
                    ->contains(fn ($stat) => $stat->meters_total - $shift < $stat->meters_installed);

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'meters_total' => 'Уменьшение приведёт к тому, что в последующих периодах приборов будет меньше, чем установлено.',
                    ]);
                }
            }

            $selectedPeriodStat ??= $substation->meterStats()
                ->make(['year' => $year, 'month' => $month])
                ->balanceGroup()->associate($balanceGroup);

            $selectedPeriodStat->fill([
                'meters_total' => $metersTotal,
                'meters_installed' => $metersInstalled,
            ])->save();

            if ($diffTotal > 0) {
                $laterStats()->increment('meters_total', $diffTotal);
            } elseif ($diffTotal < 0) {
                $laterStats()->decrement('meters_total', abs($diffTotal));
            }
        });
    }
}
