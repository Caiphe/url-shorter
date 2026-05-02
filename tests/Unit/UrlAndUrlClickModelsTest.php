<?php

use App\Models\Url;
use App\Models\UrlClick;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('url belongs to user and has many clicks', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://example.com/path?q=1',
        'short_code' => 'abc123',
    ]);

    expect($url->user->is($user))->toBeTrue();

    UrlClick::factory()->for($url)->create([
        'clicked_at' => now(),
        'via_qr' => false,
    ]);
    UrlClick::factory()->for($url)->create([
        'clicked_at' => now(),
        'via_qr' => true,
    ]);

    expect($url->fresh()->clicks)->toHaveCount(2);
});

test('url scopes active and owned by filter correctly', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Url::factory()->for($user)->create([
        'original_url' => 'https://a.test',
        'short_code' => 'aaaaaa',
    ]);
    Url::factory()->inactive()->for($user)->create([
        'original_url' => 'https://b.test',
        'short_code' => 'bbbbbb',
    ]);
    Url::factory()->for($other)->create([
        'original_url' => 'https://c.test',
        'short_code' => 'cccccc',
    ]);

    expect(Url::query()->active()->count())->toBe(2);
    expect(Url::withoutGlobalScopes()->ownedBy($user->id)->count())->toBe(2);
});

test('url click counts respect via_qr', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://example.com',
        'short_code' => 'xyz789',
    ]);

    UrlClick::factory()->count(2)->for($url)->create(['via_qr' => false]);
    UrlClick::factory()->for($url)->create(['via_qr' => true]);

    $url->refresh();

    expect($url->clicks()->count())->toBe(3);
    expect($url->clicks()->where('via_qr', false)->count())->toBe(2);
    expect($url->clicks()->where('via_qr', true)->count())->toBe(1);
});

test('url click belongs to url', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://example.com',
        'short_code' => 'qqqqqq',
    ]);
    $click = UrlClick::factory()->for($url)->create(['via_qr' => false]);

    expect($click->url->is($url))->toBeTrue();
});

test('deleting user cascades to urls and url clicks', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://example.com',
        'short_code' => 'casdel',
    ]);
    UrlClick::factory()->for($url)->create(['via_qr' => false]);

    $user->delete();

    expect(Url::query()->count())->toBe(0);
    expect(UrlClick::query()->count())->toBe(0);
});

test('short code must be unique', function () {
    $user = User::factory()->create();

    Url::factory()->for($user)->create([
        'original_url' => 'https://one.test',
        'short_code' => 'dupdup',
    ]);

    expect(fn () => Url::factory()->for($user)->create([
        'original_url' => 'https://two.test',
        'short_code' => 'dupdup',
    ]))->toThrow(QueryException::class);
});

test('factories produce six character short codes and valid defaults', function () {
    $url = Url::factory()->create();

    expect($url->short_code)->toHaveLength(6);
    expect($url->is_active)->toBeTrue();

    $click = UrlClick::factory()->create();

    expect($click->url)->toBeInstanceOf(Url::class);
    expect($click->via_qr)->toBeFalse();

    $qrClick = UrlClick::factory()->viaQr()->for($url)->create();

    expect($qrClick->via_qr)->toBeTrue();
    expect($qrClick->clicked_at)->not->toBeNull();
});
