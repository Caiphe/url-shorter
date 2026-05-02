<?php

use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RedirectController;
use App\Models\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('urls/{id}/analytics', function (int $id) {
        Url::forUser((int) Auth::id())->findOrFail($id);

        return view('analytics', ['urlId' => $id]);
    })->name('urls.analytics')->whereNumber('id');

    Route::get('urls/{id}/qr', [QrCodeController::class, 'show'])
        ->name('urls.qr')
        ->whereNumber('id');

    Route::get('urls/{id}/qr/download', [QrCodeController::class, 'download'])
        ->name('urls.qr.download')
        ->whereNumber('id');
});

require __DIR__.'/settings.php';

// Wildcard redirect routes — MUST be last
Route::get('{shortCode}', [RedirectController::class, 'redirect'])
    ->name('redirect')
    ->where('shortCode', '[A-Za-z0-9]+');

Route::get('{shortCode}/qr', [RedirectController::class, 'redirectQr'])
    ->name('redirect.qr')
    ->where('shortCode', '[A-Za-z0-9]+');
