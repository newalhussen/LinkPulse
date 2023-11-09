<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkRequest;
use App\Models\Link;
use App\Services\CodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinkController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = trim((string) $request->query('q', ''));
        $links = $user->links()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';
                $query->where(fn ($w) => $w->where('code', 'like', $like)->orWhere('title', 'like', $like)->orWhere('target_url', 'like', $like));
            })
            ->latest('id')->paginate(15)->withQueryString();

        return view('links.index', [
            'links' => $links,
            'q' => $q,
            'totals' => [
                'links' => $user->links()->count(),
                'clicks' => (int) $user->links()->sum('clicks_count'),
                'active' => $user->links()->where('is_active', true)->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            ],
        ]);
    }

    public function store(LinkRequest $request, CodeGenerator $codes): RedirectResponse
    {
        $link = $request->user()->links()->create([
            'code' => $request->input('code') ?: $codes->generate(),
            'title' => $request->input('title') ?: null,
            'target_url' => $request->input('target_url'),
            'expires_at' => $request->expiresAt(),
        ]);

        return redirect()->route('links.show', $link)->with('status', 'Short link created.');
    }

    public function show(Link $link)
    {
        $this->authorize('manage', $link);
        $days = 30;
        $from = now()->startOfDay()->subDays($days - 1);
        $perDay = $link->clicks()->where('clicked_at', '>=', $from)
            ->select(DB::raw('DATE(clicked_at) as day'), DB::raw('count(*) as n'))->groupBy('day')->pluck('n', 'day');
        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i)->toDateString();
            $series[] = ['day' => $d, 'n' => (int) ($perDay[$d] ?? 0)];
        }

        return view('links.show', [
            'link' => $link,
            'series' => $series,
            'last7' => $link->clicks()->where('clicked_at', '>=', now()->subDays(7))->count(),
            'last30' => array_sum(array_column($series, 'n')),
            'referrers' => $link->clicks()->select('referrer_host', DB::raw('count(*) as n'))->groupBy('referrer_host')->orderByDesc('n')->limit(8)->get(),
            'devices' => $link->clicks()->select('device', DB::raw('count(*) as n'))->groupBy('device')->orderByDesc('n')->get(),
            'recent' => $link->clicks()->latest('clicked_at')->latest('id')->limit(15)->get(),
        ]);
    }

    public function update(LinkRequest $request, Link $link): RedirectResponse
    {
        $this->authorize('manage', $link);
        $link->update([
            'target_url' => $request->input('target_url'),
            'title' => $request->input('title') ?: null,
            'expires_at' => $request->expiresAt(),
            'is_active' => $request->boolean('is_active', $link->is_active),
        ]);

        return back()->with('status', 'Link updated.');
    }

    public function toggle(Link $link): RedirectResponse
    {
        $this->authorize('manage', $link);
        $link->update(['is_active' => ! $link->is_active]);

        return back()->with('status', $link->is_active ? 'Link enabled.' : 'Link disabled. Visitors will see a notice instead of being redirected.');
    }

    public function destroy(Link $link): RedirectResponse
    {
        $this->authorize('manage', $link);
        $link->delete();

        return redirect()->route('dashboard')->with('status', 'Link deleted.');
    }
}
