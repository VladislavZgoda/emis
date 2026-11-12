<?php

use App\Enums\BalanceGroupCode;
use App\Models\Substation;
use App\Models\SubstationMeterStat;
use App\Models\User;

beforeEach(function () {
    seedBalanceGroups();

    $this->user = User::factory()->create();
    $this->substation = Substation::factory()->create(['name' => 'ТП-1']);
    $this->year = now()->year - 1;

    addMeterStat($this->substation, BalanceGroupCode::Household, $this->year, 3, total: 100, installed: 4);
});

function fillRow($page, string $code, string $total, string $installed)
{
    return $page
        ->type('[data-testid="input-meters_total-'.$code.'"]', $total)
        ->type('[data-testid="input-meters_installed-'.$code.'"]', $installed);
}

it('edits a row and updates the table and totals', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]');

    fillRow($page, 'household', '137', '21')
        ->click('[data-testid="save-household"]')
        ->assertMissing('[data-testid="input-meters_total-household"]')   // режим редактирования закрылся
        ->assertSee('137')
        ->assertSee('21')
        ->assertNoJavaScriptErrors();

    expect(findMeterStat($this->substation, 3, BalanceGroupCode::Household, $this->year))
        ->meters_total->toBe(137)
        ->meters_installed->toBe(21);
});

it('saves with Enter', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]');

    fillRow($page, 'household', '143', '9')
        ->keys('[data-testid="input-meters_installed-household"]', 'Enter')
        ->assertMissing('[data-testid="input-meters_total-household"]')
        ->assertSee('143');

    expect(findMeterStat($this->substation, 3, BalanceGroupCode::Household, $this->year)->meters_total)->toBe(143);
});

it('discards changes with the cancel icon', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]');

    fillRow($page, 'household', '999', '88')
        ->click('[data-testid="cancel-household"]')
        ->assertMissing('[data-testid="input-meters_total-household"]')
        ->assertDontSee('999');

    expect(findMeterStat($this->substation, 3, BalanceGroupCode::Household, $this->year))
        ->meters_total->toBe(100)
        ->meters_installed->toBe(4);
});

it('discards changes with Escape', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]');

    fillRow($page, 'household', '777', '66')
        ->keys('[data-testid="input-meters_total-household"]', 'Escape')
        ->assertMissing('[data-testid="input-meters_total-household"]')
        ->assertDontSee('777');

    expect(findMeterStat($this->substation, 3, BalanceGroupCode::Household, $this->year)->meters_total)->toBe(100);
});

it('shows the current values when editing starts', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]')
        ->assertValue('[data-testid="input-meters_total-household"]', '100')
        ->assertValue('[data-testid="input-meters_installed-household"]', '4');
});

it('does not send the request when nothing was changed', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]')
        ->click('[data-testid="save-household"]')
        ->assertMissing('[data-testid="input-meters_total-household"]')
        ->assertNoJavaScriptErrors();

    expect(SubstationMeterStat::count())->toBe(1);
});

it('allows editing only one row at a time', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]')
        ->assertDisabled('[data-testid="edit-legal_uspd"]')
        ->click('[data-testid="cancel-household"]')
        ->assertEnabled('[data-testid="edit-legal_uspd"]');
});

describe('client validation', function () {
    it('rejects installed greater than total without a request', function () {
        $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

        $page->click('[data-testid="edit-household"]');

        fillRow($page, 'household', '5', '9')
            ->click('[data-testid="save-household"]')
            ->assertSee('Установлено не может быть больше общего количества')
            ->assertPresent('[data-testid="input-meters_total-household"]');   // редактор остался открытым

        expect(findMeterStat($this->substation, 3, BalanceGroupCode::Household, $this->year)->meters_total)->toBe(100);
    });

    it('rejects an empty field', function () {
        $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

        $page->click('[data-testid="edit-household"]');

        fillRow($page, 'household', '', '4')
            ->click('[data-testid="save-household"]')
            ->assertSee('Введите целые неотрицательные числа');

        expect(findMeterStat($this->substation, 3, BalanceGroupCode::Household, $this->year)->meters_total)->toBe(100);
    });
});

it('shows a server error and keeps the editor open', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, $this->year, 5, total: 110, installed: 100);
    SubstationMeterStat::where('month', 3)->update(['meters_installed' => 0]);

    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]');

    fillRow($page, 'household', '60', '0')
        ->click('[data-testid="save-household"]')
        ->assertSee('Уменьшение приведёт')
        ->assertPresent('[data-testid="input-meters_total-household"]')
        ->assertNoJavaScriptErrors();

    expect(findMeterStat($this->substation, 3, BalanceGroupCode::Household, $this->year)->meters_total)->toBe(100)
        ->and(findMeterStat($this->substation, 5, BalanceGroupCode::Household, $this->year)->meters_total)->toBe(110);
});

it('shows the cascaded total in the later month', function () {
    addMeterStat($this->substation, BalanceGroupCode::Household, $this->year, 5, total: 120, installed: 7);

    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]');

    fillRow($page, 'household', '135', '4')                     // 100 → 135: сдвиг +35
        ->click('[data-testid="save-household"]')
        ->assertMissing('[data-testid="input-meters_total-household"]');

    $page->click('[data-testid="month-select"]')
        ->click('[data-testid="month-option-5"]')
        ->assertQueryStringHas('month', '5')
        ->assertSee('155');                                     // 120 + 35
});

it('creates a record for a month without data', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 6);   // записи за июнь нет

    $page->click('[data-testid="edit-household"]')
        ->assertValue('[data-testid="input-meters_total-household"]', '100')       // fallback из марта
        ->assertValue('[data-testid="input-meters_installed-household"]', '0');

    fillRow($page, 'household', '113', '13')
        ->click('[data-testid="save-household"]')
        ->assertMissing('[data-testid="input-meters_total-household"]')
        ->assertSee('113');

    expect(findMeterStat($this->substation, 6, BalanceGroupCode::Household, $this->year))
        ->meters_total->toBe(113)
        ->meters_installed->toBe(13);
});

it('closes the editor when the period is changed', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-household"]')
        ->assertPresent('[data-testid="input-meters_total-household"]')
        ->click('[data-testid="month-select"]')
        ->click('[data-testid="month-option-4"]')
        ->assertQueryStringHas('month', '4')
        ->assertMissing('[data-testid="input-meters_total-household"]');
});

it('edits a row of another balance group', function () {
    $page = openSubstationPage($this->substation, $this->user, $this->year, 3);

    $page->click('[data-testid="edit-odpu_gsm"]');

    fillRow($page, 'odpu_gsm', '57', '8')
        ->click('[data-testid="save-odpu_gsm"]')
        ->assertMissing('[data-testid="input-meters_total-odpu_gsm"]');

    $stat = SubstationMeterStat::whereHas('balanceGroup', fn ($q) => $q->where('code', 'odpu_gsm'))->first();

    expect($stat)->meters_total->toBe(57)->meters_installed->toBe(8);
});
