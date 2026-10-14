<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the login screen', function () {
    $this->get(route('login'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/login'));
});

it('redirects guests from protected pages to login', function () {
    $this->actingAsGuest()->get(route('home'))->assertRedirect(route('login'));
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create(['password' => '12345']);

    visit(route('login'))
        ->type('input[name=username]', $user->username)
        ->type('input[name=password]', '12345')
        ->pressAndWaitFor('Войти', 2)
        ->assertUrlIs(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('logs the user out via the menu', function () {
    $user = User::factory()->create(['password' => '12345']);

    visit(route('login'))
        ->type('input[name=username]', $user->username)
        ->type('input[name=password]', '12345')
        ->pressAndWaitFor('Войти', 2)
        ->pressAndWaitFor('Выход', 2)
        ->assertUrlIs(route('login'));

    $this->assertGuest();
});

it('requires username and password', function () {
    $this->post(route('login.store'), [])
        ->assertSessionHasErrors(['username', 'password']);
});

it('rejects an invalid password', function () {
    $user = User::factory()->create();

    visit(route('login'))
        ->type('input[name=username]', $user->username)
        ->type('input[name=password]', 'invalid password')
        ->pressAndWaitFor('Войти', 2)
        ->assertSee('Неверное имя пользователя или пароль.');

    $this->assertGuest();
});

it('does not expose substations to guests', function () {
    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->missing('substations'));
});
