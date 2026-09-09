<?php

it('renders the document language as Indonesian', function () {
    $response = $this->get('/');

    $response->assertOk();
    expect($response->getContent())->toContain('<html lang="id"');
});
