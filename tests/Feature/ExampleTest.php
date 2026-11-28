<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the public landing page offers one login flow and participant registration', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('login.form'))
        ->assertSee('Masuk ke akun')
        ->assertSee('data-nav-toggle', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('aria-controls="site-navigation"', false)
        ->assertDontSee('Masuk sebagai admin')
        ->assertDontSee('Masuk sebagai peserta')
        ->assertSee('Buat akun peserta');
});
