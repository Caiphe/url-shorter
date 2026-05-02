<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Models\UrlClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function redirect(Request $request, string $shortCode): RedirectResponse
    {
        $url = Url::query()->where('short_code', $shortCode)->firstOrFail();

        UrlClick::query()->create([
            'url_id' => $url->id,
            'clicked_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'via_qr' => false,
        ]);

        return redirect()->away($url->original_url, 302);
    }

    public function redirectQr(Request $request, string $shortCode): RedirectResponse
    {
        $url = Url::query()->where('short_code', $shortCode)->firstOrFail();

        UrlClick::query()->create([
            'url_id' => $url->id,
            'clicked_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'via_qr' => true,
        ]);

        return redirect()->away($url->original_url, 302);
    }
}
