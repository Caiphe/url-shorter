<?php

use App\Models\Url;
use App\Models\User;

test('guest is redirected to login for qr show', function () {
    $this->get(route('urls.qr', ['id' => 1]))->assertRedirect(route('login'));
});

test('guest is redirected to login for qr download', function () {
    $this->get(route('urls.qr.download', ['id' => 1]))->assertRedirect(route('login'));
});

test('authenticated owner receives inline png for show', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'short_code' => 'myshort',
    ]);

    $response = $this->actingAs($user)->get(route('urls.qr', ['id' => $url->id]));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'image/png');
    expect($response->getContent())->toStartWith("\x89PNG\r\n\x1a\n");
    expect($response->headers->get('Content-Disposition'))->toBeNull();
});

test('authenticated owner receives download with expected filename', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'short_code' => 'dlcode',
    ]);

    $response = $this->actingAs($user)->get(route('urls.qr.download', ['id' => $url->id]));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'image/png');
    expect($response->getContent())->toStartWith("\x89PNG\r\n\x1a\n");
    $disposition = $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('attachment');
    expect($disposition)->toContain('qr-dlcode.png');
});

test('user cannot show qr for another users url', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $url = Url::factory()->for($owner)->create();

    $this->actingAs($other)->get(route('urls.qr', ['id' => $url->id]))->assertNotFound();
});

test('user cannot download qr for another users url', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $url = Url::factory()->for($owner)->create();

    $this->actingAs($other)->get(route('urls.qr.download', ['id' => $url->id]))->assertNotFound();
});
