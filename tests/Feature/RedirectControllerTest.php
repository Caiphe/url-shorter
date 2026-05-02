<?php

use App\Models\Url;
use App\Models\UrlClick;
use App\Models\User;

test('redirect logs direct click and returns 302 away', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://example.com/target-page',
        'short_code' => 'abcxyz',
    ]);

    $response = $this->call('GET', '/abcxyz', [], [], [], [
        'HTTP_REFERER' => 'https://referrer.example/from',
        'HTTP_USER_AGENT' => 'IntegrationTest/1.0',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('https://example.com/target-page');

    $click = UrlClick::query()->where('url_id', $url->id)->sole();
    expect($click->via_qr)->toBeFalse();
    expect($click->ip_address)->not->toBeNull();
    expect($click->user_agent)->toBe('IntegrationTest/1.0');
    expect($click->referrer)->toBe('https://referrer.example/from');
    expect($click->clicked_at)->not->toBeNull();
});

test('redirect qr logs qr click and returns 302 away', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://destination.test/here',
        'short_code' => 'qrpath',
    ]);

    $response = $this->get('/qrpath/qr');

    $response->assertStatus(302);
    $response->assertRedirect('https://destination.test/here');

    $click = UrlClick::query()->where('url_id', $url->id)->sole();
    expect($click->via_qr)->toBeTrue();
});

test('inactive short link returns not found', function () {
    $user = User::factory()->create();
    $url = Url::factory()->inactive()->for($user)->create([
        'original_url' => 'https://hidden.example/',
        'short_code' => 'inactv',
    ]);

    $this->get('/inactv')->assertNotFound();
    $this->get('/inactv/qr')->assertNotFound();
});

test('unknown short code returns not found', function () {
    $this->get('/nomnom')->assertNotFound();
});
