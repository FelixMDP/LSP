<?php

test('the public landing page offers the admin, participant, and registration flows', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Masuk sebagai admin')
        ->assertSee('Masuk sebagai peserta')
        ->assertSee('Daftar sebagai peserta');
});
