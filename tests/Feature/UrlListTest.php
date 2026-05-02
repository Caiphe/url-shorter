<?php

use App\Models\Url;
use App\Models\UrlClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('url list shows aggregated click counts from withCount', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://unique-destination.example/path',
        'short_code' => 'uniq01',
    ]);
    UrlClick::factory()->count(11)->for($url)->create();

    Livewire::actingAs($user)
        ->test('url-list')
        ->assertSee('uniq01', escape: false)
        ->assertSee('11', escape: false);
});

test('url list search filters by destination or short code', function () {
    $user = User::factory()->create();
    Url::factory()->for($user)->create([
        'original_url' => 'https://alpha-only.example/page',
        'short_code' => 'aaaaaa',
    ]);
    Url::factory()->for($user)->create([
        'original_url' => 'https://beta-only.example/page',
        'short_code' => 'bbbbbb',
    ]);

    Livewire::actingAs($user)
        ->test('url-list')
        ->set('search', 'alpha-only')
        ->assertSee('aaaaaa', escape: false)
        ->assertDontSee('bbbbbb', escape: false);
});

test('url list delete removes the url for the authenticated owner', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create(['short_code' => 'delme1']);

    Livewire::actingAs($user)
        ->test('url-list')
        ->call('deleteUrl', $url->id);

    expect(Url::withoutGlobalScopes()->whereKey($url->id)->exists())->toBeFalse();
});

test('url list responds to url created event', function () {
    $user = User::factory()->create();
    Url::factory()->for($user)->create(['short_code' => 'first1']);

    Livewire::actingAs($user)
        ->test('url-list')
        ->assertSee('first1', escape: false)
        ->dispatch('url-created')
        ->assertOk();
});

test('url list does not show another users links', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    Url::factory()->for($owner)->create(['short_code' => 'owner1']);

    Livewire::actingAs($other)
        ->test('url-list')
        ->assertDontSee('owner1', escape: false);
});

test('url list qr column uses lazy thumbnail and modal download link without target blank', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create(['short_code' => 'qrcol1']);

    $component = Livewire::actingAs($user)->test('url-list');

    $html = $component->html();

    expect($html)->toContain('loading="lazy"');
    expect($html)->toContain('whitespace-nowrap align-top');

    $htmlAfterOpen = $component->call('openQrPreview', $url->id)->html();

    expect(preg_match_all(
        '/<a\s[^>]*href="[^"]*\/urls\/'.$url->id.'\/qr\/download"[^>]*>/',
        $htmlAfterOpen,
        $downloadAnchors,
        PREG_SET_ORDER
    ))->toBeGreaterThan(0);

    foreach ($downloadAnchors as [$fullTag]) {
        expect($fullTag)->not->toContain('target="_blank"');
    }
});

test('url list qr preview ignores another users url id', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $url = Url::factory()->for($owner)->create(['short_code' => 'owned1']);

    Livewire::actingAs($other)
        ->test('url-list')
        ->call('openQrPreview', $url->id)
        ->assertSet('qrPreviewUrlId', null)
        ->assertSet('qrPreviewShortCode', '');
});
