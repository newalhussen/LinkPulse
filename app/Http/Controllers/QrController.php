<?php

namespace App\Http\Controllers;

use App\Models\Link;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;

class QrController extends Controller
{
    /** SVG QR code for the short URL, inline or as a download (?download=1). Scales to any print size. */
    public function __invoke(Request $request, Link $link)
    {
        $this->authorize('manage', $link);
        $svg = (new Writer(new ImageRenderer(new RendererStyle(512, 2), new SvgImageBackEnd)))->writeString($link->shortUrl());
        $headers = ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'private, max-age=300'];
        if ($request->boolean('download')) {
            $headers['Content-Disposition'] = 'attachment; filename="linkpulse-'.preg_replace('/[^A-Za-z0-9_-]/', '', $link->code).'.svg"';
        }

        return response($svg, 200, $headers);
    }
}
