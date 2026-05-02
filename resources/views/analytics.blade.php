<x-layouts::app :title="__('Analytics')">
    <div class="flex flex-col gap-6">
        <flux:heading size="lg">{{ __('URL analytics') }}</flux:heading>
        <livewire:url-chart :url-id="$urlId" />
    </div>
</x-layouts::app>
