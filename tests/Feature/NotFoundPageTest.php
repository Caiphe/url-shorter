<?php

test('standalone 404 view is rendered for unmatched paths', function (string $path) {
    $response = $this->get($path);

    $response->assertNotFound();
    $response->assertSee('Page not found', false);
    $response->assertSee('Go to sign in', false);
})->with([
    'nested path' => ['/no-such/nested/page'],
    'unknown short code' => ['/notarealshortcode999'],
]);
