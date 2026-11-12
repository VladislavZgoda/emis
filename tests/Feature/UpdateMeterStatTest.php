<?php

use App\Enums\BalanceGroupCode;
use App\Models\Substation;
use App\Models\SubstationMeterStat;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 6, 15));

    seedBalanceGroups();
    signIn();

    $this->substation = Substation::factory()->create();
    $this->url = route('substations.meter-stats.update', $this->substation);
});

/** Месячная серия: [месяц => [всего, установлено]]. */
function seedSeries(Substation $substation, array $series, BalanceGroupCode $code = BalanceGroupCode::Household, int $year = 2026): void
{
    foreach ($series as $month => [$total, $installed]) {
        addMeterStat($substation, $code, $year, $month, total: $total, installed: $installed);
    }
}

describe('saving', function () {
    it('creates a record when there is none', function () {
        $this->put($this->url, updatePayload(['meters_total' => 50, 'meters_installed' => 4]))
            ->assertRedirect();

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(50)
            ->meters_installed->toBe(4);
    });

    it('creates a record with zeros', function () {
        $this->put($this->url, updatePayload(['meters_total' => 0, 'meters_installed' => 0]))
            ->assertRedirect();

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(0)
            ->meters_installed->toBe(0);
    });

    it('updates an existing record', function () {
        seedSeries($this->substation, [3 => [100, 4]]);

        $this->put($this->url, updatePayload(['meters_total' => 120, 'meters_installed' => 6]));

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(120)
            ->meters_installed->toBe(6);
        expect($this->substation->meterStats()->count())->toBe(1);
    });

    it('accepts total lower than the previous installed when installed is lowered too', function () {
        seedSeries($this->substation, [3 => [20, 10]]);

        $this->put($this->url, updatePayload(['meters_total' => 5, 'meters_installed' => 3]))
            ->assertSessionHasNoErrors();

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(5)
            ->meters_installed->toBe(3);
    });

    it('accepts numeric strings as sent by the form', function () {
        $this->put($this->url, [
            'balance_group' => 'household',
            'year' => '2026',
            'month' => '3',
            'meters_total' => '40',
            'meters_installed' => '7',
        ])->assertSessionHasNoErrors();

        expect(findMeterStat($this->substation, 3)->meters_total)->toBe(40);
    });

    it('redirects back to the page the user came from', function () {
        $from = route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]);

        $this->from($from)->put($this->url, updatePayload())
            ->assertRedirect($from);
    });

    it('works for every balance group', function (BalanceGroupCode $code) {
        $this->put($this->url, updatePayload(['balance_group' => $code->value, 'meters_total' => 9, 'meters_installed' => 2]));

        expect(findMeterStat($this->substation, 3, $code))->meters_total->toBe(9);
    })->with(BalanceGroupCode::cases());
});

describe('cascade', function () {
    it('shifts later periods up by the difference of the total', function () {
        seedSeries($this->substation, [1 => [100, 1], 3 => [110, 10], 5 => [120, 7]]);

        $this->put($this->url, updatePayload(['meters_total' => 115, 'meters_installed' => 10]));

        $stats = $this->substation->meterStats()->orderBy('month')->get();

        expect($stats->pluck('meters_total')->all())->toBe([100, 115, 125])
            ->and($stats->pluck('meters_installed')->all())->toBe([1, 10, 7]);
    });

    it('shifts later periods down when the total is decreased', function () {
        seedSeries($this->substation, [1 => [100, 1], 3 => [110, 10], 5 => [120, 7]]);

        $this->put($this->url, updatePayload(['meters_total' => 105, 'meters_installed' => 10]));

        expect($this->substation->meterStats()->orderBy('month')->pluck('meters_total')->all())
            ->toBe([100, 105, 115]);
    });

    it('does not cascade when only installed changes', function () {
        seedSeries($this->substation, [3 => [110, 10], 5 => [120, 7]]);

        $this->put($this->url, updatePayload(['meters_total' => 110, 'meters_installed' => 12]));

        expect(findMeterStat($this->substation, 5))
            ->meters_total->toBe(120)
            ->meters_installed->toBe(7);
    });

    it('does not cascade when nothing changes', function () {
        seedSeries($this->substation, [3 => [110, 10], 5 => [120, 7]]);

        $this->put($this->url, updatePayload(['meters_total' => 110, 'meters_installed' => 10]))
            ->assertSessionHasNoErrors();

        expect($this->substation->meterStats()->orderBy('month')->pluck('meters_total')->all())
            ->toBe([110, 120]);
    });

    it('bases a new record on the latest earlier total and shifts later ones by the difference', function () {
        // Январь 100, май 120. Вносим март с 105: сдвиг +5, май станет 125
        seedSeries($this->substation, [1 => [100, 1], 5 => [120, 7]]);

        $this->put($this->url, updatePayload(['meters_total' => 105, 'meters_installed' => 5]));

        $stats = $this->substation->meterStats()->orderBy('month')->get();

        expect($stats->pluck('month')->all())->toBe([1, 3, 5])
            ->and($stats->pluck('meters_total')->all())->toBe([100, 105, 125]);
    });

    it('cascades across years', function () {
        seedSeries($this->substation, [6 => [50, 0]], year: 2025);
        seedSeries($this->substation, [2 => [60, 0]], year: 2026);

        $this->put($this->url, updatePayload(['year' => 2025, 'month' => 7, 'meters_total' => 70, 'meters_installed' => 0]));

        expect($this->substation->meterStats()->orderBy('year')->orderBy('month')->pluck('meters_total')->all())
            ->toBe([50, 70, 80]);
    });

    it('does not touch earlier periods, other groups and other substations', function () {
        $other = Substation::factory()->create();

        seedSeries($this->substation, [1 => [100, 2], 3 => [110, 10], 5 => [120, 7]]);
        seedSeries($this->substation, [5 => [60, 6]], BalanceGroupCode::LegalGsm);
        seedSeries($other, [5 => [50, 5]]);

        $this->put($this->url, updatePayload(['meters_total' => 130, 'meters_installed' => 10]));

        expect(findMeterStat($this->substation, 1))->meters_total->toBe(100)->meters_installed->toBe(2)
            ->and(findMeterStat($this->substation, 5, BalanceGroupCode::LegalGsm))->meters_total->toBe(60)
            ->and(findMeterStat($other, 5))->meters_total->toBe(50);
    });
});

describe('decrease protection', function () {
    it('rejects a decrease that would leave later totals below installed', function () {
        // Март 100, май 110 при 100 установленных. Уменьшение марта на 40 даст в мае 70 < 100
        seedSeries($this->substation, [3 => [100, 0], 5 => [110, 100]]);

        $this->put($this->url, updatePayload(['meters_total' => 60, 'meters_installed' => 0]))
            ->assertSessionHasErrors('meters_total');

        expect(findMeterStat($this->substation, 3))->meters_total->toBe(100)
            ->and(findMeterStat($this->substation, 5))->meters_total->toBe(110);
    });

    it('allows a decrease that leaves later totals exactly equal to installed', function () {
        // Май 110 при 70 установленных, уменьшение на 40 даёт ровно 70
        seedSeries($this->substation, [3 => [100, 0], 5 => [110, 70]]);

        $this->put($this->url, updatePayload(['meters_total' => 60, 'meters_installed' => 0]))
            ->assertSessionHasNoErrors();

        expect(findMeterStat($this->substation, 5)->meters_total)->toBe(70);
    });

    it('checks every later period, not only the nearest one', function () {
        seedSeries($this->substation, [3 => [100, 0], 4 => [105, 5], 5 => [110, 100]]);

        $this->put($this->url, updatePayload(['meters_total' => 60, 'meters_installed' => 0]))
            ->assertSessionHasErrors('meters_total');

        expect($this->substation->meterStats()->orderBy('month')->pluck('meters_total')->all())
            ->toBe([100, 105, 110]);
    });

    it('does not block a decrease because of other groups or substations', function () {
        $other = Substation::factory()->create();

        seedSeries($this->substation, [3 => [100, 0]]);
        seedSeries($this->substation, [5 => [110, 100]], BalanceGroupCode::LegalGsm);
        seedSeries($other, [5 => [110, 100]]);

        $this->put($this->url, updatePayload(['meters_total' => 60, 'meters_installed' => 0]))
            ->assertSessionHasNoErrors();

        expect(findMeterStat($this->substation, 3)->meters_total)->toBe(60);
    });

    it('does not check later periods when the total grows', function () {
        seedSeries($this->substation, [3 => [100, 0], 5 => [110, 100]]);

        $this->put($this->url, updatePayload(['meters_total' => 150, 'meters_installed' => 0]))
            ->assertSessionHasNoErrors();

        expect(findMeterStat($this->substation, 5)->meters_total)->toBe(160);
    });
});

describe('validation', function () {
    it('rejects invalid input and writes nothing', function (array $override, string $field) {
        $this->put($this->url, updatePayload($override))
            ->assertSessionHasErrors($field);

        expect(SubstationMeterStat::count())->toBe(0);
    })->with([
        'installed greater than total' => [['meters_total' => 5, 'meters_installed' => 6], 'meters_installed'],
        'unknown group' => [['balance_group' => 'nope'], 'balance_group'],
        'missing group' => [['balance_group' => null], 'balance_group'],
        'negative total' => [['meters_total' => -1], 'meters_total'],
        'negative installed' => [['meters_installed' => -1], 'meters_installed'],
        'fractional total' => [['meters_total' => 1.5], 'meters_total'],
        'empty total' => [['meters_total' => ''], 'meters_total'],
        'empty installed' => [['meters_installed' => ''], 'meters_installed'],
        'month zero' => [['month' => 0], 'month'],
        'month 13' => [['month' => 13], 'month'],
        'month not a number' => [['month' => 'abc'], 'month'],
        'year too small' => [['year' => 1999], 'year'],
        'future year' => [['year' => 2027], 'year'],
        'future month' => [['month' => 7], 'month'],
    ]);

    it('rejects an empty payload', function () {
        $this->put($this->url, [])
            ->assertSessionHasErrors(['balance_group', 'year', 'month', 'meters_total', 'meters_installed']);
    });

    it('allows the current month', function () {
        $this->put($this->url, updatePayload(['month' => 6]))->assertSessionHasNoErrors();

        expect(findMeterStat($this->substation, 6))->not->toBeNull();
    });
});
