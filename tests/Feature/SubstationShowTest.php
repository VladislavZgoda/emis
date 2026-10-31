<?php

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    seedBalanceGroups();
    signIn();
    $this->substation = Substation::factory()->create(['name' => 'ТП-1']);
});

it('renders the page with only id and name of the substation', function () {
    $this->get(route('substations.show', $this->substation))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('substations/show')
            ->where('substation', [
                'id' => $this->substation->id,
                'name' => 'ТП-1',
            ])
            ->has('meterStats', 5));
});

it('uses current month and year by default', function () {
    $this->travelTo(now()->setDate(2026, 3, 15));

    $this->get(route('substations.show', $this->substation))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.year', 2026)
            ->where('filters.month', 3));
});

it('returns zeros for every group when there is no data at all', function () {
    $this->get(route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('totals', ['meters_total' => 0, 'meters_installed' => 0])
            ->has('meterStats', 5, fn (Assert $row) => $row
                ->where('meters_total', 0)
                ->where('meters_installed', 0)
                ->etc()));
});

it('shows stats of the selected month', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 3, total: 100, installed: 7);
    addMeterStat($this->substation, BalanceGroupCode::LegalUspd, 2026, 3, total: 20, installed: 2);

    $this->get(route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meterStats.0.balance_group', 'БЫТ')
            ->where('meterStats.0.meters_total', 100)
            ->where('meterStats.0.meters_installed', 7)
            ->where('meterStats.1.balance_group', 'Юридические лица (УСПД)')
            ->where('meterStats.1.meters_total', 20)
            ->where('meterStats.1.meters_installed', 2)
            ->where('totals', ['meters_total' => 120, 'meters_installed' => 9]));
});

it('falls back to the latest known total but resets installed to zero', function () {
    // Данные есть за январь
    addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 1, total: 90, installed: 5);

    // Смотрим март
    $this->get(route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meterStats.0.meters_total', 90)
            ->where('meterStats.0.meters_installed', 0));
});

it('picks the most recent earlier record across years', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, 2024, 12, total: 50);
    addMeterStat($this->substation, BalanceGroupCode::Household, 2025, 6, total: 70);

    $this->get(route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 2]))
        ->assertInertia(fn (Assert $page) => $page->where('meterStats.0.meters_total', 70));
});

it('ignores records from later periods', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 5, total: 200, installed: 9);

    $this->get(route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meterStats.0.meters_total', 0)
            ->where('meterStats.0.meters_installed', 0));
});

it('does not mix stats of different substations', function () {
    $other = Substation::factory()->create();
    addMeterStat($other, BalanceGroupCode::Household, 2026, 3, total: 999, installed: 99);

    $this->get(route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]))
        ->assertInertia(fn (Assert $page) => $page->where('totals.meters_total', 0));
});

it('applies fallback per group independently', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 3, total: 100, installed: 4);
    addMeterStat($this->substation, BalanceGroupCode::LegalUspd, 2026, 1, total: 30, installed: 3);

    $this->get(route('substations.show', ['substation' => $this->substation, 'year' => 2026, 'month' => 3]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meterStats.0.meters_installed', 4)
            ->where('meterStats.1.meters_total', 30)
            ->where('meterStats.1.meters_installed', 0));
});

it('rejects invalid period', function (array $query) {
    $this->get(route('substations.show', ['substation' => $this->substation] + $query))
        ->assertSessionHasErrors(array_key_first($query));
})->with([
    'month too big' => [['month' => 99]],
    'month zero' => [['month' => 0]],
    'year too small' => [['year' => 1999]],
    'not a number' => [['month' => 'abc']],
]);

it('fails loudly when balance groups are not seeded', function () {
    BalanceGroup::query()->delete();

    $this->withoutExceptionHandling();

    $this->get(route('substations.show', $this->substation));
})->throws(RuntimeException::class);
