<?php

namespace App\Models;

use Database\Factories\SubstationMeterStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $substation_id
 * @property int $balance_group_id
 * @property int $year
 * @property int $month
 * @property int $meters_total
 * @property int $meters_installed
 */
#[Fillable('substation_id', 'balance_group_id', 'year', 'month', 'meters_total', 'meters_installed')]
class SubstationMeterStat extends Model
{
    /** @use HasFactory<SubstationMeterStatFactory> */
    use HasFactory;

    /** @return BelongsTo<Substation, $this> */
    public function substation(): BelongsTo
    {
        return $this->belongsTo(Substation::class);
    }

    /** @return BelongsTo<BalanceGroup, $this> */
    public function balanceGroup(): BelongsTo
    {
        return $this->belongsTo(BalanceGroup::class);
    }

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'meters_total' => 'integer',
            'meters_installed' => 'integer',
        ];
    }
}
