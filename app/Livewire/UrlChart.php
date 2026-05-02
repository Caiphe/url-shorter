<?php

namespace App\Livewire;

use App\Models\Url;
use App\Models\UrlClick;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UrlChart extends Component
{
    public int $urlId;

    public function mount(int $urlId): void
    {
        $this->urlId = $urlId;

        Url::query()
            ->withoutGlobalScopes()
            ->where('user_id', (int) auth()->id())
            ->whereKey($urlId)
            ->firstOrFail();
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    public function chartSeries(): array
    {
        $stats = UrlClick::query()
            ->where('url_id', $this->urlId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        $days = collect();
        foreach (now()->subDays(30)->daysUntil(now()) as $date) {
            $dateString = $date->toDateString();
            $days->put($dateString, (int) $stats->get($dateString, 0));
        }

        return [
            'labels' => $days->keys()->values()->all(),
            'values' => $days->values()->all(),
        ];
    }

    public function render(): View
    {
        $series = $this->chartSeries();

        return view('livewire.url-chart', [
            'chartLabels' => $series['labels'],
            'chartValues' => $series['values'],
        ]);
    }
}
