<div class="flex w-full flex-col gap-4">
    <flux:heading size="lg">{{ __('Clicks (last 30 days)') }}</flux:heading>

    <div wire:ignore class="h-80 w-full">
        <div
            x-data="urlClickBarChart(@js($chartLabels), @js($chartValues))"
            class="h-full w-full"
        >
            <canvas x-ref="canvas" class="h-full max-h-80 w-full"></canvas>
        </div>
    </div>
</div>
