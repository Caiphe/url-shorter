<?php

namespace App\Http\Controllers;

use App\Models\Url;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class QrCodeController extends Controller
{
    public function show(int $id): Response
    {
        $url = Url::forUser((int) Auth::id())->findOrFail($id);

        return response($this->getQrImage($url), 200, [
            'Content-Type' => 'image/png',
        ]);
    }

    public function download(int $id): Response
    {
        $url = Url::forUser((int) Auth::id())->findOrFail($id);

        $filename = 'qr-'.$url->short_code.'.png';

        return response($this->getQrImage($url), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function getQrImage(Url $url): string
    {
        $targetUrl = route('redirect.qr', ['shortCode' => $url->short_code], absolute: true);

        $writer = new Writer(new GDLibRenderer(300, 1));

        return $writer->writeString($targetUrl, ecLevel: ErrorCorrectionLevel::L());
    }
}
