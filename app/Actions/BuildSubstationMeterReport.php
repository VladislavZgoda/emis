<?php

namespace App\Actions;

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use Illuminate\Support\Collection;
use RuntimeException;

class BuildSubstationMeterReport
{
    /**
     * @return Collection<int, array{balance_group: string, meters_total: int, meters_installed: int}>
     */
    public function __invoke(Substation $substation, int $year, int $month): Collection
    {
        $groupIds = BalanceGroup::query()
            ->whereIn('code', array_column(BalanceGroupCode::cases(), 'value'))
            ->pluck('id', 'code');

        $latest = $substation->meterStats()
            ->where(fn ($q) => $q
                ->where('year', '<', $year)
                ->orWhere(fn ($q) => $q
                    ->where('year', $year)
                    ->where('month', '<=', $month)))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->unique('balance_group_id')
            ->keyBy('balance_group_id');

        return collect(BalanceGroupCode::cases())->map(
            function (BalanceGroupCode $code) use ($groupIds, $latest, $year, $month) {
                $groupId = $groupIds->get($code->value)
                    ?? throw new RuntimeException("Balance group [{$code->value}] not found. Run the seeder.");

                $stat = $latest->get($groupId);

                $isCurrentPeriod = $stat !== null
                    && (int) $stat->year === $year
                    && (int) $stat->month === $month;

                return [
                    'balance_group' => $code->label(),
                    'meters_total' => $stat->meters_total ?? 0,
                    'meters_installed' => $isCurrentPeriod ? $stat->meters_installed : 0,
                ];
            }
        );
    }
}
