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
                        <td class="px-4 py-3 whitespace-nowrap align-top">
                            <div class="flex flex-col items-center gap-2">
                                <img
                                    src="{{ route('urls.qr', ['id' => $url->id]) }}"
                                    alt="{{ __('QR Code') }}"
                                    width="64"
                                    height="64"
                                    class="rounded shadow-sm"
                                    loading="lazy"
                                >
                                <a
                                    href="{{ route('urls.qr.download', ['id' => $url->id]) }}"
                                    download
                                    class="text-xs text-slate-400 transition-colors hover:text-slate-600"
                                >
                                    {{ __('Download') }}
                                </a>
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

    <div>
        {{ $urls->links() }}
    </div>
</div>
