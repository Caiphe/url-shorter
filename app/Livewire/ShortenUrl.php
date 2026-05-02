<?php

namespace App\Livewire;

use App\Services\UrlShortenerService;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ShortenUrl extends Component
{
    public string $url = '';

    public string $shortenedUrl = '';

    public function shorten(UrlShortenerService $service): void
    {
        $this->resetErrorBag('url');

        $this->validate([
            'url' => ['required', 'string', 'active_url'],
        ]);

        $limiter = RateLimiter::limiter('shorten');
        $resolved = $limiter !== null ? $limiter(request()) : null;
        $rateLimitKey = null;
        $decaySeconds = 60;
        $maxAttempts = 30;

        if ($resolved !== null && ! $resolved instanceof Unlimited) {
            $limit = Collection::wrap($resolved)->first();
            $rateLimitKey = md5('shorten'.$limit->key);
            $decaySeconds = $limit->decaySeconds;
            $maxAttempts = $limit->maxAttempts;

            if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
                $this->addError(
                    'url',
                    __('Too many shorten attempts. Please try again in :seconds seconds.', [
                        'seconds' => RateLimiter::availableIn($rateLimitKey),
                    ])
                );

                return;
            }
        }

        try {
            $this->shortenedUrl = $service->shorten($this->url);
        } catch (\InvalidArgumentException $e) {
            $this->addError('url', $e->getMessage());

            return;
        }

        if ($rateLimitKey !== null) {
            RateLimiter::hit($rateLimitKey, $decaySeconds);
        }

        $this->reset('url');
        $this->dispatch('url-created');
    }

    public function render(): View
    {
        return view('livewire.shorten-url');
    }
}
