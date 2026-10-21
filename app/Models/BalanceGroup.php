<?php

namespace App\Models;

use Database\Factories\BalanceGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 */
#[Fillable('code', 'name')]
class BalanceGroup extends Model
{
    /** @use HasFactory<BalanceGroupFactory> */
    use HasFactory;
}
