<?php

use App\Models\User;

it('stamps the login time in the session', function () {
    $user = User::factory()->create(['password' => 'secret']);

    $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'secret',
    ])->assertSessionHas('logged_in_at');
});

it('allows a session within the lifetime', function () {
    signIn()->get(route('home'))->assertOk();
});

it('logs out a session older than 3 days', function () {
    $this->freezeTime();

    $this->actingAs(User::factory()->create())
        ->withSession(['logged_in_at' => now()->subDays(3)->subSecond()->timestamp])
        ->get(route('home'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('keeps a session at exactly 3 days', function () {
    $this->freezeTime();

    $this->actingAs(User::factory()->create())
        ->withSession(['logged_in_at' => now()->subDays(3)->timestamp])
        ->get(route('home'))
        ->assertOk();
});

it('logs out when the login timestamp is missing', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
