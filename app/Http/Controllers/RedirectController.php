<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\ClickTracker;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request, string $code, ClickTracker $tracker)
    {
        $link = Link::where('code', $code)->first();
        abort_if($link === null, 404);
        if (! $link->isLive()) {
            return response()->view('links.unavailable', ['link' => $link], 410);
        }
        try {
            $tracker->record($link, $request);
        } catch (\Throwable $e) {
            report($e); // analytics must never stop a redirect
        }

        // 302 so browsers do not cache the destination: edits, expiry and disabling take effect immediately.
        return redirect()->away($link->target_url, 302)->withHeaders(['Cache-Control' => 'no-store']);
    }
}
