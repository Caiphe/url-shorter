<x-layouts::app :title="__('Analytics')">
    <div class="flex flex-col gap-4">
        <flux:heading size="lg">{{ __('URL analytics') }}</flux:heading>
        <flux:text>{{ __('URL ID: :id', ['id' => $urlId]) }}</flux:text>
    </div>
</x-layouts::app>
