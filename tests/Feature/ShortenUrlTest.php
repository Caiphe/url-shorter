<?php

use App\Models\Url;
use App\Models\User;
use Livewire\Livewire;

test('shorten requires a valid active url', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('shorten-url')
        ->set('url', '')
        ->call('shorten')
        ->assertHasErrors('url');

    Livewire::test('shorten-url')
        ->set('url', 'not-a-valid-url')
        ->call('shorten')
        ->assertHasErrors('url');
});

test('shorten persists url clears input dispatches link created and exposes short link', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $test = Livewire::test('shorten-url')
        ->set('url', 'https://example.com/my-page')
        ->call('shorten')
        ->assertHasNoErrors()
        ->assertSet('url', '')
        ->assertDispatched('url-created');

    expect($test->get('shortenedUrl'))->not->toBeEmpty();

    expect(Url::withoutGlobalScopes()->where('user_id', $user->id)->count())->toBe(1);
});

test('start another clears the short link and shows the shorten form again', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('shorten-url')
        ->set('url', 'https://example.com/another-page')
        ->call('shorten')
        ->assertHasNoErrors()
        ->assertSet('shortenedUrl', fn (string $value): bool => $value !== '')
        ->call('startAnother')
        ->assertSet('shortenedUrl', '');
});
