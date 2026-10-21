<?php

namespace App\Models;

use Database\Factories\SubstationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable('name')]
class Substation extends Model
{
    /** @use HasFactory<SubstationFactory> */
    use HasFactory;

    /** @return HasMany<SubstationMeterStat, $this> */
    public function meterStats(): HasMany
    {
        return $this->hasMany(SubstationMeterStat::class);
    }
}
