<?php

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UrlShortenerService
{
    /**
     * Create a short link for the authenticated user and return its public URL.
     */
    public function shorten(string $originalUrl): string
    {
        $userId = Auth::id();

        if ($userId === null) {
            throw new InvalidArgumentException(__('You must be signed in to shorten URLs.'));
        }

        $url = $this->generate($originalUrl, (int) $userId);

        return route('redirect', ['shortCode' => $url->short_code]);
    }

    public function generate(string $originalUrl, int $userId): Url
    {
        if (filter_var($originalUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Invalid URL.');
        }

        do {
            $code = Str::random(6);
        } while (Url::withoutGlobalScopes()->where('short_code', $code)->exists());

        return Url::create([
            'original_url' => $originalUrl,
            'short_code' => $code,
            'user_id' => $userId,
            'is_active' => true,
        ]);
    }
}
