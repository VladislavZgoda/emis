<?php

use App\Models\Substation;
use App\Models\User;

beforeEach(function () {
    $user = User::factory()->create(['password' => '12345']);
    $this->user = $user;
});

it('creates a substation through the dialog', function () {
    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->click('[aria-label="Добавить ТП"]')
        ->fill('#name', 'ТП-1')
        ->pressAndWaitFor('Создать', 2)
        ->assertSee('ТП-1')
        ->assertNoSmoke();

    $this->assertDatabaseHas('substations', ['name' => 'ТП-1']);
});

it('shows validation error when name is empty', function () {
    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->click('[aria-label="Добавить ТП"]')
        ->pressAndWaitFor('Создать', 2)
        ->assertSee('Поле Наименование обязательно для заполнения.')
        ->assertNoSmoke();

    $this->assertDatabaseCount('substations', 0);
});

it('keeps the dialog open and shows an error for a duplicate name', function () {
    Substation::factory()->create(['name' => 'ТП-1']);

    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->click('[aria-label="Добавить ТП"]')
        ->fill('#name', 'ТП-1')
        ->pressAndWaitFor('Создать', 2)
        ->assertSee('Значение поля Наименование уже занято.')
        ->assertNoSmoke();

    $this->assertDatabaseCount('substations', 1);
});

it('prefills the current name and renames a substation through the edit dialog', function () {
    $page = visit(route('login'));
    $substation = Substation::factory()->create(['name' => 'ТП-1']);

    loginThroughBrowser($page, $this->user)
        ->click('[data-testid="rename-substation-'.$substation->id.'"]')
        ->assertValue('input[name=name]', 'ТП-1')
        ->clear('#name')
        ->fill('#name', 'ТП-2')
        ->pressAndWaitFor('Сохранить', 2)
        ->assertSee('ТП-2')
        ->assertDontSee('ТП-1')
        ->assertNoSmoke();

    expect($substation->fresh()->name)->toBe('ТП-2');
});

it('allows renaming a substation to its own current name', function () {
    $page = visit(route('login'));
    $substation = Substation::factory()->create();

    loginThroughBrowser($page, $this->user)
        ->click('[data-testid="rename-substation-'.$substation->id.'"]')
        ->fill('#name', $substation->name)
        ->pressAndWaitFor('Сохранить', 2)
        ->assertNoSmoke();

    expect($substation->fresh()->name)->toBe($substation->name);
});

it('rejects renaming to an existing name', function () {
    Substation::factory()->create(['name' => 'ТП-1']);
    $target = Substation::factory()->create(['name' => 'ТП-2']);

    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->click('[data-testid="rename-substation-'.$target->id.'"]')
        ->fill('#name', 'ТП-1')
        ->pressAndWaitFor('Сохранить', 2)
        ->assertSee('Значение поля Наименование уже занято.')
        ->assertNoSmoke();

    expect($target->fresh()->name)->toBe('ТП-2');
});

it('deletes a substation after confirmation', function () {
    $page = visit(route('login'));
    $substation = Substation::factory()->create();

    loginThroughBrowser($page, $this->user)
        ->click('[data-testid="delete-substation-'.$substation->id.'"]')
        ->pressAndWaitFor('Удалить', 2)
        ->assertDontSee($substation->name)
        ->assertNoSmoke();

    $this->assertDatabaseCount('substations', 0);
});

it('cancels deletion when the cancel button is clicked', function () {
    $page = visit(route('login'));
    $substation = Substation::factory()->create();

    loginThroughBrowser($page, $this->user)
        ->click('[data-testid="delete-substation-'.$substation->id.'"]')
        ->pressAndWaitFor('Отмена', 2)
        ->assertSee($substation->name)
        ->assertNoSmoke();

    $this->assertDatabaseCount('substations', 1);
});

it('filters substations with the search input', function () {
    Substation::factory()->create(['name' => 'ТП-1']);
    Substation::factory()->create(['name' => 'ТП-2']);

    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->fill('#search', '1')
        ->assertSee('ТП-1')
        ->assertDontSee('ТП-2')
        ->assertNoSmoke();
});

it('shows empty state when no substations match the search', function () {
    Substation::factory()->create();

    $page = visit(route('login'));

    loginThroughBrowser($page, $this->user)
        ->fill('#search', 'xyz')
        ->assertSee('Ничего не найдено')
        ->assertNoSmoke();
});

it('rejects a name longer than 15 characters', function () {
    signIn($this->user)->post(route('substations.store'), ['name' => str_repeat('a', 16)])
        ->assertSessionHasErrors('name');
});
