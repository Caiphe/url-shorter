<?php

use App\Livewire\UrlChart;
use App\Models\Url;
use App\Models\UrlClick;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('url chart exposes thirty one daily buckets for the last thirty days window', function () {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(UrlChart::class, ['urlId' => $url->id])
        ->assertViewHas('chartLabels', fn (array $labels): bool => count($labels) === 31)
        ->assertViewHas('chartValues', fn (array $values): bool => count($values) === 31);
});

test('url chart fills missing days with zero using keyed pluck lookup', function () {
    $this->travelTo(now()->parse('2026-05-15 12:00:00'));

    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create();

    UrlClick::factory()->for($url)->create([
        'created_at' => now()->subDays(5),
        'clicked_at' => now()->subDays(5),
    ]);

    Livewire::actingAs($user)
        ->test(UrlChart::class, ['urlId' => $url->id])
        ->assertViewHas('chartValues', function (array $values): bool {
            return array_sum($values) === 1 && count(array_filter($values, fn (int $v): bool => $v === 0)) === 30;
        });
});

test('url chart rejects urls that do not belong to the authenticated user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $url = Url::factory()->for($owner)->create();

    expect(fn () => Livewire::actingAs($intruder)->test(UrlChart::class, ['urlId' => $url->id]))
        ->toThrow(ModelNotFoundException::class);
});
