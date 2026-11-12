<?php

use App\Enums\BalanceGroupCode;
use App\Models\BalanceGroup;
use App\Models\Substation;
use App\Models\SubstationMeterStat;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\BalanceGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function signIn(?User $user = null)
{
    return test()
        ->actingAs($user ?? User::factory()->create())
        ->withSession(['logged_in_at' => now()->timestamp]);
}

/**
 * Выполняет вход в систему через браузер.
 * Принимает УЖЕ СОЗДАННУЮ страницу.
 */
function loginThroughBrowser(PendingAwaitablePage $page, User $user, string $password = UserFactory::PASSWORD): AwaitableWebpage
{
    return $page->type('input[name=username]', $user->username)
        ->type('input[name=password]', $password)
        ->press('Войти')
        ->assertPathIsNot('/login');
}

function openSubstationPage(Substation $substation, User $user, int $year, int $month)
{
    return loginThroughBrowser(visit('/login'), $user)
        ->navigate(route('substations.show', compact('substation', 'year', 'month')))
        ->assertSee($substation->name);
}

function seedBalanceGroups(): void
{
    test()->seed(BalanceGroupSeeder::class);
}

function addMeterStat(
    Substation $substation,
    BalanceGroupCode $code,
    int $year,
    int $month,
    int $total,
    int $installed = 0,
): SubstationMeterStat {
    return SubstationMeterStat::factory()
        ->for($substation)
        ->forGroup($code)
        ->forPeriod($year, $month)
        ->create(['meters_total' => $total, 'meters_installed' => $installed]);
}

function findMeterStat(
    Substation $substation,
    int $month,
    BalanceGroupCode $code = BalanceGroupCode::Household,
    int $year = 2026,
): ?SubstationMeterStat {
    return $substation->meterStats()
        ->whereBelongsTo(BalanceGroup::where('code', $code->value)->firstOrFail())
        ->where('year', $year)
        ->where('month', $month)
        ->first();
}

function storePayload(array $override = []): array
{
    return array_merge([
        'balance_group' => 'household',
        'year' => 2026,
        'month' => 6,
        'amount' => 5,
    ], $override);
}

function updatePayload(array $override = []): array
{
    return array_merge([
        'balance_group' => 'household',
        'year' => 2026,
        'month' => 3,
        'meters_total' => 100,
        'meters_installed' => 10,
    ], $override);
}
