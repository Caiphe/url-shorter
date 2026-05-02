<?php

namespace App\Livewire;

use App\Models\Url;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UrlList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $qrPreviewUrlId = null;

    public string $qrPreviewShortCode = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[On('url-created')]
    public function refreshUrlList(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Url>
     */
    public function loadUrls(): LengthAwarePaginator
    {
        $userId = (int) auth()->id();

        return Url::query()
            ->withoutGlobalScopes()
            ->where('user_id', $userId)
            ->withCount([
                'clicks as total_clicks',
                'clicks as recent_clicks' => function (Builder $query): void {
                    $query->where('created_at', '>=', now()->subDays(7));
                },
            ])
            ->when($this->search !== '', function (Builder $query): void {
                $escaped = addcslashes($this->search, '%_\\');
                $term = '%'.$escaped.'%';
                $query->where(function (Builder $q) use ($term): void {
                    $q->where('original_url', 'like', $term)
                        ->orWhere('short_code', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function deleteUrl(int $urlId): void
    {
        $url = Url::query()
            ->withoutGlobalScopes()
            ->where('user_id', (int) auth()->id())
            ->whereKey($urlId)
            ->first();

        if ($url !== null) {
            if ($this->qrPreviewUrlId === $url->id) {
                $this->modal('qr-preview')->close();
                $this->reset('qrPreviewUrlId', 'qrPreviewShortCode');
            }

            $url->delete();
        }
    }

    public function openQrPreview(int $urlId): void
    {
        $url = Url::query()
            ->withoutGlobalScopes()
            ->where('user_id', (int) auth()->id())
            ->whereKey($urlId)
            ->first();

        if ($url === null) {
            return;
        }

        $this->qrPreviewUrlId = $url->id;
        $this->qrPreviewShortCode = $url->short_code;
        $this->modal('qr-preview')->show();
    }

    public function closeQrPreview(): void
    {
        $this->reset('qrPreviewUrlId', 'qrPreviewShortCode');
    }

    public function render(): View
    {
        return view('livewire.url-list', [
            'urls' => $this->loadUrls(),
        ]);
    }
}
