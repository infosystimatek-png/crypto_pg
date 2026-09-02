<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('Vaultgate')
        ->assertSee('Accept stablecoin payments', false);
});
