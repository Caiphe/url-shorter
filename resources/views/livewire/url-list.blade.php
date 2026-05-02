<div class="flex w-full flex-col gap-4">
    <flux:heading size="lg">{{ __('Your links') }}</flux:heading>

    <flux:field>
        <flux:label>{{ __('Search') }}</flux:label>
        <flux:input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Filter by destination or short code…') }}"
        />
    </flux:field>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-zinc-600 dark:text-zinc-400">{{ __('Destination') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-zinc-600 dark:text-zinc-400">{{ __('Short link') }}</th>
                    <th class="px-4 py-3 text-center font-medium text-zinc-600 dark:text-zinc-400">{{ __('QR code') }}</th>
                    <th class="px-4 py-3 text-end font-medium text-zinc-600 dark:text-zinc-400">{{ __('Total clicks') }}</th>
                    <th class="px-4 py-3 text-end font-medium text-zinc-600 dark:text-zinc-400">{{ __('Recent (7d)') }}</th>
                    <th class="px-4 py-3 text-end font-medium text-zinc-600 dark:text-zinc-400">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                @forelse ($urls as $url)
                    <tr wire:key="url-row-{{ $url->id }}">
                        <td class="max-w-xs truncate px-4 py-3 text-zinc-900 dark:text-zinc-100" title="{{ $url->original_url }}">
                            {{ \Illuminate\Support\Str::limit($url->original_url, 56) }}
                        </td>
                        <td class="px-4 py-3">
                            <a
                                href="{{ route('redirect', ['shortCode' => $url->short_code]) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="break-all text-indigo-600 underline decoration-indigo-600/30 underline-offset-2 hover:decoration-indigo-600 dark:text-indigo-400 dark:decoration-indigo-400/30 dark:hover:decoration-indigo-400"
                            >
                                {{ route('redirect', ['shortCode' => $url->short_code]) }}
                            </a>
                        </td>
                        <td class="whitespace-nowrap align-top px-4 py-3">
                            <div class="flex flex-col items-center gap-1">
                                <flux:tooltip :content="__('Open preview & download')" position="top">
                                    <button
                                        type="button"
                                        wire:click="openQrPreview({{ $url->id }})"
                                        class="group rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-800"
                                    >
                                        <span class="sr-only">{{ __('Open QR code preview') }}</span>
                                        <img
                                            src="{{ route('urls.qr', ['id' => $url->id]) }}"
                                            alt=""
                                            width="64"
                                            height="64"
                                            loading="lazy"
                                            class="rounded-lg shadow-sm ring-1 ring-zinc-200/80 transition group-hover:ring-2 group-hover:ring-indigo-400/60 dark:ring-zinc-600 dark:group-hover:ring-indigo-400/50"
                                        >
                                    </button>
                                </flux:tooltip>
                                <flux:text size="sm" class="text-zinc-400 dark:text-zinc-500">{{ __('Tap to enlarge') }}</flux:text>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-end tabular-nums text-zinc-900 dark:text-zinc-100">
                            {{ $url->total_clicks }}
                        </td>
                        <td class="px-4 py-3 text-end tabular-nums text-zinc-900 dark:text-zinc-100">
                            {{ $url->recent_clicks }}
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="flex flex-wrap justify-end gap-2">
                                <flux:button size="sm" variant="ghost" :href="route('urls.analytics', ['id' => $url->id])" wire:navigate>
                                    {{ __('Analytics') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="deleteUrl({{ $url->id }})"
                                    wire:confirm="{{ __('Delete this short link?') }}"
                                >
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                            @if ($search !== '')
                                {{ __('No links match your search.') }}
                            @else
                                {{ __('You have not created any short links yet.') }}
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal
        name="qr-preview"
        variant="floating"
        class="max-w-md !p-0 sm:max-w-lg"
        wire:close="closeQrPreview"
    >
        @if ($qrPreviewUrlId !== null)
            <div class="flex flex-col">
                <div class="border-b border-zinc-200 px-6 pb-4 pt-5 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('QR code') }}</flux:heading>
                    <flux:subheading class="mt-1 font-mono text-sm">
                        {{ $qrPreviewShortCode }}
                    </flux:subheading>
                </div>

                <div class="flex flex-col items-center gap-3 px-6 py-8">
                    <div
                        class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-inner dark:border-zinc-600 dark:bg-zinc-100"
                    >
                        <img
                            src="{{ route('urls.qr', ['id' => $qrPreviewUrlId]) }}"
                            alt="{{ __('QR code for :code', ['code' => $qrPreviewShortCode]) }}"
                            width="280"
                            height="280"
                            class="size-56 sm:size-64"
                            loading="eager"
                        >
                    </div>
                    <flux:text size="sm" class="max-w-xs text-center text-zinc-500 dark:text-zinc-400">
                        {{ __('Scanning opens your short link via the QR path so clicks are tracked.') }}
                    </flux:text>
                </div>

                <div
                    class="flex flex-col gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-5 dark:border-zinc-700 dark:bg-zinc-900/60"
                >
                    <flux:button
                        variant="primary"
                        class="w-full"
                        icon="arrow-down-tray"
                        :href="route('urls.qr.download', ['id' => $qrPreviewUrlId])"
                    >
                        {{ __('Download PNG') }}
                    </flux:button>
                    <flux:modal.close>
                        <flux:button variant="ghost" class="w-full">{{ __('Close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    <div>
        {{ $urls->links() }}
    </div>
</div>
