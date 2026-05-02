<div class="mx-auto w-full max-w-2xl">
    <div
        class="rounded-2xl border border-zinc-600/50 bg-zinc-800/70 p-10 shadow-xl ring-1 ring-black/5 sm:p-12 dark:border-zinc-700/80 dark:bg-zinc-900/90 dark:ring-white/10"
    >
        <div class="mb-10 space-y-3 text-center">
            <flux:heading size="xl" class="!text-2xl sm:!text-3xl" level="1">
                {{ __('Clip your next link') }}
            </flux:heading>
            <flux:text class="mx-auto max-w-md text-base text-zinc-600 dark:text-zinc-400">
                {{ __('Paste a long URL—walk away with a short one. Stats live on your link shelf.') }}
            </flux:text>
        </div>

        @if ($shortenedUrl !== '')
            <div class="space-y-8">
                <flux:field>
                    <flux:label class="text-base">{{ __('Your short link') }}</flux:label>
                    <flux:input
                        type="url"
                        readonly
                        :value="$shortenedUrl"
                        copyable
                        variant="filled"
                        class="font-mono text-sm"
                        input:class="!py-3 !text-base sm:!text-lg !h-12"
                    />
                </flux:field>

                <flux:button type="button" variant="ghost" class="w-full !text-base" wire:click="startAnother">
                    {{ __('Clip another URL') }}
                </flux:button>
            </div>
        @else
            <form wire:submit="shorten" class="space-y-8">
                <flux:field>
                    <flux:label class="text-base">{{ __('Destination URL') }}</flux:label>
                    <flux:input
                        type="url"
                        wire:model="url"
                        icon="link"
                        placeholder="https://example.com/your-long-url"
                        variant="filled"
                        input:class="!py-3 !text-base sm:!text-lg !h-12"
                    />
                    <flux:error name="url" />
                </flux:field>

                <flux:button
                    variant="primary"
                    type="submit"
                    class="w-full !h-12 !text-base"
                    wire:loading.attr="disabled"
                    wire:target="shorten"
                >
                    <span wire:loading.remove wire:target="shorten">{{ __('Clip it') }}</span>
                    <span wire:loading wire:target="shorten">{{ __('Clipping…') }}</span>
                </flux:button>
            </form>
        @endif
    </div>
</div>
