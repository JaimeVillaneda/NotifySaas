<?php

it('redirects the home page to the Filament panel', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin');
});
