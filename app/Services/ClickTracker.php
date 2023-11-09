<?php

namespace App\Services;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;

/** Records a visit with the minimum data needed for analytics: time, referrer host and device class. */
class ClickTracker
{
    public function isBot(?string $userAgent): bool
    {
        if ($userAgent === null || $userAgent === '') {
            return true;
        }

        return (bool) preg_match('/bot|crawl|spider|slurp|preview|fetch|monitor|curl|wget|python-requests|facebookexternalhit|embedly|headless/i', $userAgent);
    }

    public function device(?string $userAgent): string
    {
        $ua = (string) $userAgent;
        if (preg_match('/ipad|tablet|kindle|silk|(android(?!.*mobile))/i', $ua)) {
            return 'tablet';
        }
        if (preg_match('/mobi|iphone|ipod|android|blackberry|phone/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /** Host of the Referer header, or null when direct or when it is our own site. */
    public function referrerHost(?string $referer, string $ownHost): ?string
    {
        if (! $referer) {
            return null;
        }
        $host = parse_url($referer, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }
        $host = strtolower(substr($host, 0, 190));

        return $host === strtolower($ownHost) ? null : $host;
    }

    public function record(Link $link, Request $request): bool
    {
        $ua = $request->userAgent();
        if ($this->isBot($ua)) {
            return false; // crawlers and link previews are not visitors
        }
        Click::create([
            'link_id' => $link->id,
            'clicked_at' => now(),
            'referrer_host' => $this->referrerHost($request->headers->get('referer'), $request->getHost()),
            'device' => $this->device($ua),
        ]);
        Link::whereKey($link->id)->increment('clicks_count');

        return true;
    }
}
