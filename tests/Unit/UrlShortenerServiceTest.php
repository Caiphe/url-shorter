<?php

use App\Models\Url;
use App\Models\User;
use App\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('generate throws invalid argument exception for invalid url', function () {
    $service = app(UrlShortenerService::class);

    expect(fn () => $service->generate('not-a-valid-url', 1))
        ->toThrow(InvalidArgumentException::class);
});

test('generate creates active url with unique short code', function () {
    $user = User::factory()->create();
    $service = app(UrlShortenerService::class);

    $url = $service->generate('https://example.com/path', $user->id);

    expect($url)->toBeInstanceOf(Url::class);
    expect($url->original_url)->toBe('https://example.com/path');
    expect($url->user_id)->toBe($user->id);
    expect($url->is_active)->toBeTrue();
    expect($url->short_code)->toHaveLength(6);
});

test('shorten returns absolute short url for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $service = app(UrlShortenerService::class);
    $shortUrl = $service->shorten('https://example.org/deep/page');

    $url = Url::withoutGlobalScopes()->where('user_id', $user->id)->first();

    expect($url)->not->toBeNull();
    expect($shortUrl)->toContain($url->short_code);
});

test('shorten throws when guest', function () {
    $service = app(UrlShortenerService::class);

    expect(fn () => $service->shorten('https://example.com/'))
        ->toThrow(InvalidArgumentException::class);
});
