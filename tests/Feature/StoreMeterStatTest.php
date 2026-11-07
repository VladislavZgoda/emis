<?php

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use App\Models\SubstationMeterStat;

beforeEach(function () {
    // travelTo до signIn(): signIn пишет logged_in_at = now()
    $this->travelTo(now()->setDate(2026, 6, 15));

    seedBalanceGroups();
    signIn();

    $this->substation = Substation::factory()->create();
    $this->url = route('substations.meter-stats.store', $this->substation);
});

describe('recording', function () {
    it('creates a record from zero when there is no history', function () {
        $this->post($this->url, storePayload(['month' => 3, 'amount' => 10]))
            ->assertRedirect();

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(10)
            ->meters_installed->toBe(10);
    });

    it('carries the total from the previous period', function () {
        addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 1, total: 100, installed: 3);

        $this->post($this->url, storePayload(['month' => 3, 'amount' => 10]));

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(110)
            ->meters_installed->toBe(10);
    });

    it('carries the total from a previous year', function () {
        addMeterStat($this->substation, BalanceGroupCode::Household, 2025, 12, total: 80);

        $this->post($this->url, storePayload(['month' => 1, 'amount' => 5]));

        expect(findMeterStat($this->substation, 1)->meters_total)->toBe(85);
    });

    it('increments an existing record', function () {
        addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 3, total: 100, installed: 4);

        $this->post($this->url, storePayload(['month' => 3, 'amount' => 10]));

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(110)
            ->meters_installed->toBe(14);
        expect($this->substation->meterStats()->count())->toBe(1);
    });

    it('accumulates several submissions for the same period', function () {
        $this->post($this->url, storePayload(['month' => 3, 'amount' => 4]));
        $this->post($this->url, storePayload(['month' => 3, 'amount' => 6]));

        expect(findMeterStat($this->substation, 3))
            ->meters_total->toBe(10)
            ->meters_installed->toBe(10);
    });

    it('cascades the total to later periods without touching installed', function () {
        addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 1, total: 100, installed: 1);
        addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 3, total: 110, installed: 10);
        addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 5, total: 120, installed: 7);

        $this->post($this->url, storePayload(['month' => 2, 'amount' => 5]));

        $stats = $this->substation->meterStats()->orderBy('month')->get();

        expect($stats->pluck('month')->all())->toBe([1, 2, 3, 5])
            ->and($stats->pluck('meters_total')->all())->toBe([100, 105, 115, 125])
            ->and($stats->pluck('meters_installed')->all())->toBe([1, 5, 10, 7]);
    });

    it('cascades across years', function () {
        addMeterStat($this->substation, BalanceGroupCode::Household, 2025, 6, total: 50);
        addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 2, total: 60);

        $this->post($this->url, storePayload(['year' => 2025, 'month' => 7, 'amount' => 10]));

        expect($this->substation->meterStats()->orderBy('year')->orderBy('month')->pluck('meters_total')->all())
            ->toBe([50, 60, 70]);
    });

    it('does not touch earlier periods', function () {
        addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 1, total: 100, installed: 2);

        $this->post($this->url, storePayload(['month' => 3, 'amount' => 10]));

        expect(findMeterStat($this->substation, 1))
            ->meters_total->toBe(100)
            ->meters_installed->toBe(2);
    });

    it('does not touch other balance groups', function () {
        addMeterStat($this->substation, BalanceGroupCode::LegalGsm, 2026, 5, total: 60, installed: 6);

        $this->post($this->url, storePayload(['month' => 3, 'amount' => 10]));

        expect(findMeterStat($this->substation, 5, BalanceGroupCode::LegalGsm))
            ->meters_total->toBe(60)
            ->meters_installed->toBe(6);
    });

    it('does not touch other substations', function () {
        $other = Substation::factory()->create();
        addMeterStat($other, BalanceGroupCode::Household, 2026, 5, total: 50, installed: 5);

        $this->post($this->url, storePayload(['month' => 3, 'amount' => 10]));

        expect(findMeterStat($other, 5))->meters_total->toBe(50)->meters_installed->toBe(5);
        expect($other->meterStats()->count())->toBe(1);
    });

    it('works for every balance group', function (BalanceGroupCode $code) {
        $this->post($this->url, storePayload(['balance_group' => $code->value, 'month' => 3, 'amount' => 2]));

        expect(findMeterStat($this->substation, 3, $code))->meters_total->toBe(2);
    })->with(BalanceGroupCode::cases());

    it('accepts numeric strings as sent by the form component', function () {
        $this->post($this->url, [
            'balance_group' => 'household', 'year' => '2026', 'month' => '3', 'amount' => '7',
        ])->assertRedirect();

        expect(findMeterStat($this->substation, 3)->meters_total)->toBe(7);
    });

    it('allows the current month', function () {
        $this->post($this->url, storePayload(['month' => 6]))->assertRedirect();

        expect(findMeterStat($this->substation, 6))->not->toBeNull();
    });

    it('redirects back to the page the user came from', function () {
        $from = route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]);

        $this->from($from)->post($this->url, storePayload(['month' => 3]))
            ->assertRedirect($from);
    });
});

describe('validation', function () {
    it('rejects invalid input and writes nothing', function (array $override, string $field) {
        $this->post($this->url, storePayload($override))
            ->assertSessionHasErrors($field);

        expect(SubstationMeterStat::count())->toBe(0);
    })->with([
        'unknown group' => [['balance_group' => 'nope'], 'balance_group'],
        'missing group' => [['balance_group' => null], 'balance_group'],
        'zero amount' => [['amount' => 0], 'amount'],
        'negative amount' => [['amount' => -3], 'amount'],
        'fractional amount' => [['amount' => 1.5], 'amount'],
        'empty amount' => [['amount' => ''], 'amount'],
        'month zero' => [['month' => 0], 'month'],
        'month 13' => [['month' => 13], 'month'],
        'month not a number' => [['month' => 'abc'], 'month'],
        'year too small' => [['year' => 1999], 'year'],
        'future year' => [['year' => 2027], 'year'],
        'future month' => [['month' => 7], 'month'],
    ]);

    it('rejects an empty payload', function () {
        $this->post($this->url, [])
            ->assertSessionHasErrors(['balance_group', 'year', 'month', 'amount']);
    });
});

describe('access', function () {
    it('returns 404 for an unknown substation', function () {
        $this->post(route('substations.meter-stats.store', 999999), storePayload())
            ->assertNotFound();
    });

    it('fails when balance groups are not seeded', function () {
        BalanceGroup::query()->delete();

        $this->post($this->url, storePayload())->assertNotFound();

        expect(SubstationMeterStat::count())->toBe(0);
    });
});
