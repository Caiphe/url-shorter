<?php

test('login screen can be reached without authentication', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});
