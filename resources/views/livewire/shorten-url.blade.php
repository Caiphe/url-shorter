<div class="max-w-2xl space-y-6">
    <flux:heading size="lg">{{ __('Shorten a URL') }}</flux:heading>

    <form wire:submit="shorten" class="space-y-4">
        <flux:field>
            <flux:label>{{ __('Destination URL') }}</flux:label>
            <flux:input type="url" wire:model="url" placeholder="https://example.com" />
            <flux:error name="url" />
        </flux:field>

        <flux:button
            variant="primary"
            type="submit"
            wire:loading.attr="disabled"
            wire:target="shorten"
        >
            <span wire:loading.remove wire:target="shorten">{{ __('Shorten') }}</span>
            <span wire:loading wire:target="shorten">{{ __('Shortening...') }}</span>
        </flux:button>
    </form>

    @if ($shortenedUrl)
        <div x-data="{ copied: false }" class="space-y-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text class="font-medium text-zinc-600 dark:text-zinc-400">{{ __('Your short link') }}</flux:text>
            <div class="flex flex-wrap items-center gap-2">
                <flux:text class="break-all">{{ $shortenedUrl }}</flux:text>
                <flux:button
                    type="button"
                    size="sm"
                    variant="ghost"
                    x-on:click="navigator.clipboard.writeText(@js($shortenedUrl)); copied = true"
                >
                    <span x-show="!copied">{{ __('Copy') }}</span>
                    <span x-show="copied">{{ __('Copied!') }}</span>
                </flux:button>
            </div>
        </div>
    @endif
</div>
