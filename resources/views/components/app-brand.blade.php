@props([
    'compact' => false,
])

<a
    href="{{ route('home') }}"
    wire:navigate
    {{ $attributes->class([
        'shrink-0 font-bold tracking-tight text-zinc-900 transition hover:opacity-90 dark:text-white',
        'text-lg' => $compact,
        'text-xl sm:text-2xl' => ! $compact,
    ]) }}
>
    {{ config('app.name') }}
</a>
