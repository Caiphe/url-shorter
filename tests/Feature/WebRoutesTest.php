<?php

use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

test('guests are redirected to login for urls analytics', function () {
    $this->get(route('urls.analytics', ['id' => 1]))
        ->assertRedirect(route('login'));
});

test('authenticated owner can view urls analytics', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('urls.analytics', ['id' => $url->id]))
        ->assertOk()
        ->assertSee((string) $url->id, escape: false);
});

test('authenticated user cannot view analytics for another users url', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $url = Url::factory()->for($owner)->create();

    $this->actingAs($other)
        ->get(route('urls.analytics', ['id' => $url->id]))
        ->assertNotFound();
});

test('short link redirect works for guests', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'original_url' => 'https://public.example/page',
        'short_code' => 'pub123',
    ]);

    $this->get('/pub123')
        ->assertRedirect('https://public.example/page');
});

test('shorten rate limit blocks after thirty successful shortens in one minute', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $key = md5('shorten'.(string) $user->getAuthIdentifier());
    RateLimiter::clear($key);

    for ($i = 0; $i < 30; $i++) {
        Livewire::test('shorten-url')
            ->set('url', 'https://example.com/'.uniqid('', true))
            ->call('shorten')
            ->assertHasNoErrors();
    }

    Livewire::test('shorten-url')
        ->set('url', 'https://example.com/rate-capped')
        ->call('shorten')
        ->assertHasErrors('url');
});
