<?php

use App\Enums\BalanceGroupCode;
use App\Models\Substation;
use App\Models\SubstationMeterStat;
use App\Models\User;

beforeEach(function () {
    seedBalanceGroups();

    $this->user = User::factory()->create(['password' => '12345']);
    $this->substation = Substation::factory()->create(['name' => 'ТП-1']);
    $this->year = now()->year - 1;
});

function submitInstallation($page, string $groupCode, string $amount)
{
    return $page
        ->click('Установить приборы')
        ->assertSee('Установка приборов учёта')
        ->click('[data-testid="group-select"]')
        ->click('[data-testid="group-option-'.$groupCode.'"]')
        ->type('#amount', $amount)
        ->click('[data-testid="dialog-submit"]');
}

it('records installed meters and updates the table', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, $this->year, 3, total: 100, installed: 4);

    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);
    $page->assertSee('ТП-1');

    submitInstallation($page, 'household', '37')
        ->assertDontSee('Установка приборов учёта')   // диалог закрылся
        ->assertSee('137')                            // 100 + 37
        ->assertNoJavaScriptErrors();

    expect($this->substation->meterStats()->first())
        ->meters_total->toBe(137)
        ->meters_installed->toBe(41);
});

it('records installations for another balance group', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    submitInstallation($page, 'odpu_gsm', '23')
        ->assertDontSee('Установка приборов учёта')
        ->assertSee('23');

    $stat = SubstationMeterStat::first();

    expect($stat->balanceGroup->code)->toBe('odpu_gsm')
        ->and($stat->meters_total)->toBe(23);
});

it('uses the period selected on the page as the default', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 5);

    submitInstallation($page, 'household', '11')
        ->assertDontSee('Установка приборов учёта');

    expect($this->substation->meterStats()->first())
        ->year->toBe($this->year)
        ->month->toBe(5);
});

it('follows the period when the filter is changed before opening the dialog', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 5);

    $page->click('[data-testid="month-select"]')
        ->click('[data-testid="month-option-2"]')
        ->assertQueryStringHas('month', '2');

    submitInstallation($page, 'household', '12')
        ->assertDontSee('Установка приборов учёта');

    expect($this->substation->meterStats()->first()->month)->toBe(2);
});

it('cascades a record in a past month to later months', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, $this->year, 1, total: 100, installed: 1);
    addMeterStat($this->substation, BalanceGroupCode::Household, $this->year, 3, total: 110, installed: 10);

    $page = openSubstationPage($this->substation, $this->user, $this->year, 2);

    submitInstallation($page, 'household', '5')
        ->assertDontSee('Установка приборов учёта')
        ->assertSee('105');

    $page->click('[data-testid="month-select"]')
        ->click('[data-testid="month-option-3"]')
        ->assertQueryStringHas('month', '3')
        ->assertSee('115');
});

it('keeps the dialog open and shows an error for an empty amount', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('Установить приборы')
        ->type('#amount', '')
        ->click('[data-testid="dialog-submit"]')
        ->assertSee('Установка приборов учёта')
        ->assertNoJavaScriptErrors();

    expect(SubstationMeterStat::count())->toBe(0);
});

it('resets the dialog after it is reopened', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('Установить приборы')
        ->type('#amount', '')
        ->click('[data-testid="dialog-submit"]')
        ->assertSee('Количество обязательно для заполнения')
        ->keys('#amount', 'Escape')
        ->assertDontSee('Установка приборов учёта')
        ->click('Установить приборы')
        ->assertSee('Установка приборов учёта')
        ->assertDontSee('Количество обязательно для заполнения')
        ->assertValue('#amount', '1');
});

it('disables months that have not come yet in the current year', function () {
    expect(now()->month)->toBeLessThan(12);

    $page = openSubstationPage($this->substation, $this->user, now()->year, now()->month);

    $future = now()->month + 1;

    $page->click('Установить приборы')
        ->click('[data-testid="dialog-month-select"]')
        ->assertAttribute('[data-testid="dialog-month-option-'.$future.'"]', 'aria-disabled', 'true');
})->skip(fn () => now()->month === 12, 'В декабре будущих месяцев нет');
