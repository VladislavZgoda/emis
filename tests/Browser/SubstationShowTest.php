<?php

use App\Enums\BalanceGroupCode;
use App\Models\Substation;
use App\Models\User;

beforeEach(function () {
    seedBalanceGroups();
    $this->user = User::factory()->create(['password' => '12345']);
    $this->substation = Substation::factory()->create(['name' => 'ТП-1']);

    addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 3, total: 100, installed: 7);
    addMeterStat($this->substation, BalanceGroupCode::OdpuGsm, 2026, 3, total: 12, installed: 1);
});

it('renders the meter stats table without javascript errors', function () {
    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->click('ТП-1')
        ->assertSee('ТП-1')
        ->assertSee('Балансовая группа')
        ->assertSee('БЫТ')
        ->assertSee('Юридические лица (УСПД)')
        ->assertSee('Юридические лица (GSM-модем)')
        ->assertSee('ОДПУ (УСПД)')
        ->assertSee('ОДПУ (GSM-модем)')
        ->assertSee('Итого')
        ->assertNoSmoke();
});

it('shows the selected period in the filters', function () {
    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->navigate(route('substations.show', [
            'substation' => $this->substation,
            'year' => 2026,
            'month' => 3,
        ]))
        ->assertSee('Март')
        ->assertSee('2026');
});

it('updates the table when the month is changed', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, 2026, 4, total: 111, installed: 8);

    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->navigate(route('substations.show', [
            'substation' => $this->substation,
            'year' => 2026,
            'month' => 3,
        ]))
        ->assertSee('ТП-1')
        ->click('@month-select')
        ->click('@month-option-4')
        ->assertQueryStringHas('month', '4')
        ->assertSee('111')
        ->assertNoSmoke();
});

it('shows latest totals for a month without data', function () {
    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->navigate(route('substations.show', [
            'substation' => $this->substation,
            'year' => 2026,
            'month' => 6,
        ]))
        ->assertSee('100')
        ->assertNoSmoke();
});
