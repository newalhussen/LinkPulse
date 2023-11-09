@extends('layouts.app')
@section('title', $link->title ?: $link->code)
@section('content')
@php
    $badge = ['active' => 'bg-emerald-100 text-emerald-800', 'disabled' => 'bg-slate-200 text-slate-700', 'expired' => 'bg-amber-100 text-amber-800'];
    $max = max(1, max(array_column($series, 'n')));
    $deviceTotal = max(1, $devices->sum('n'));
    $referrerTotal = max(1, $referrers->sum('n'));
@endphp
<a href="{{ route('dashboard') }}" class="text-sm text-slate-500 hover:underline">← All links</a>

<div class="mt-2 flex flex-wrap items-start gap-3">
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <h1 class="truncate text-2xl font-semibold">{{ $link->title ?: $link->code }}</h1>
            <span class="badge {{ $badge[$link->status()] }}">{{ ucfirst($link->status()) }}</span>
        </div>
        <div class="mt-1 flex items-center gap-2 text-sm">
            <a href="{{ $link->shortUrl() }}" target="_blank" rel="noopener" class="font-mono text-brand-600 hover:underline">{{ $link->shortUrl() }}</a>
            <button type="button" class="btn !px-2 !py-0.5 text-xs" data-copy="{{ $link->shortUrl() }}">Copy</button>
        </div>
        <p class="mt-1 break-all text-xs text-slate-500">→ {{ $link->target_url }}</p>
    </div>
    <form method="POST" action="{{ route('links.toggle', $link) }}">@csrf @method('PATCH')
        <button class="btn">{{ $link->is_active ? 'Disable' : 'Enable' }}</button>
    </form>
    <form method="POST" action="{{ route('links.destroy', $link) }}" onsubmit="return confirm('Delete this link and its statistics? The short URL will stop working.')">@csrf @method('DELETE')
        <button class="btn btn-danger">Delete</button>
    </form>
</div>

<div class="mt-5 grid gap-4 lg:grid-cols-[1fr_260px]">
    <div class="space-y-4">
        <div class="grid grid-cols-3 gap-3">
            @foreach ([['All time', $link->clicks_count], ['Last 7 days', $last7], ['Last 30 days', $last30]] as [$label, $value])
                <div class="card !p-4"><div class="text-xs text-slate-500">{{ $label }}</div><div class="mt-1 text-2xl font-semibold tabular-nums">{{ number_format($value) }}</div></div>
            @endforeach
        </div>

        <section class="card">
            <h2 class="mb-3 text-sm font-semibold">Clicks per day · last 30 days</h2>
            <div class="flex h-36 items-end gap-[3px] border-b border-slate-200" role="img" aria-label="Bar chart of daily clicks for the last 30 days, {{ $last30 }} in total">
                @foreach ($series as $d)
                    <div class="flex-1 rounded-t bg-brand-500/80 hover:bg-brand-600" style="height: {{ $d['n'] === 0 ? 2 : max(4, round($d['n'] / $max * 100)) }}%; {{ $d['n'] === 0 ? 'opacity:.25' : '' }}" title="{{ \Illuminate\Support\Carbon::parse($d['day'])->format('j M') }}: {{ $d['n'] }} click{{ $d['n'] === 1 ? '' : 's' }}"></div>
                @endforeach
            </div>
            <div class="mt-1 flex justify-between text-[11px] text-slate-500"><span>{{ \Illuminate\Support\Carbon::parse($series[0]['day'])->format('j M') }}</span><span>Today</span></div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2">
            <section class="card">
                <h2 class="mb-2 text-sm font-semibold">Top referrers</h2>
                @forelse ($referrers as $r)
                    <div class="mb-2 text-sm"><div class="flex justify-between"><span class="truncate">{{ $r->referrer_host ?? 'Direct / unknown' }}</span><span class="tabular-nums text-slate-500">{{ $r->n }}</span></div>
                        <div class="mt-0.5 h-1.5 rounded bg-slate-100"><div class="h-full rounded bg-brand-500" style="width: {{ round($r->n / $referrerTotal * 100) }}%"></div></div></div>
                @empty <p class="text-sm text-slate-500">No clicks yet.</p> @endforelse
            </section>
            <section class="card">
                <h2 class="mb-2 text-sm font-semibold">Devices</h2>
                @forelse ($devices as $d)
                    <div class="mb-2 text-sm"><div class="flex justify-between"><span class="capitalize">{{ $d->device }}</span><span class="tabular-nums text-slate-500">{{ round($d->n / $deviceTotal * 100) }}%</span></div>
                        <div class="mt-0.5 h-1.5 rounded bg-slate-100"><div class="h-full rounded bg-brand-500" style="width: {{ round($d->n / $deviceTotal * 100) }}%"></div></div></div>
                @empty <p class="text-sm text-slate-500">No clicks yet.</p> @endforelse
            </section>
        </div>

        <section class="card">
            <h2 class="mb-2 text-sm font-semibold">Recent clicks</h2>
            @forelse ($recent as $c)
                <div class="flex justify-between border-b border-slate-100 py-1.5 text-sm last:border-0"><span>{{ $c->clicked_at->format('j M Y, H:i') }}</span><span class="text-slate-500">{{ $c->referrer_host ?? 'Direct' }} · {{ $c->device }}</span></div>
            @empty <p class="text-sm text-slate-500">Clicks appear here as soon as someone opens the link.</p> @endforelse
        </section>
    </div>

    <aside class="space-y-4">
        <section class="card text-center">
            <img src="{{ route('links.qr', $link) }}" alt="QR code for {{ $link->shortUrl() }}" class="mx-auto size-44">
            <a class="btn mt-3 w-full" href="{{ route('links.qr', ['link' => $link, 'download' => 1]) }}">Download QR (SVG)</a>
        </section>
        <form method="POST" action="{{ route('links.update', $link) }}" class="card space-y-3" novalidate>
            @csrf @method('PUT')
            <h2 class="text-sm font-semibold">Edit</h2>
            <div><label for="target_url" class="mb-1 block text-xs font-medium text-slate-600">Destination</label>
                <input id="target_url" name="target_url" value="{{ old('target_url', $link->target_url) }}" class="input @error('target_url') input-error @enderror">
                @error('target_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
            <div><label for="title" class="mb-1 block text-xs font-medium text-slate-600">Title</label>
                <input id="title" name="title" value="{{ old('title', $link->title) }}" maxlength="120" class="input"></div>
            <div><label for="expires_on" class="mb-1 block text-xs font-medium text-slate-600">Expires on (end of day, {{ config('app.timezone') }})</label>
                <input id="expires_on" name="expires_on" type="date" value="{{ old('expires_on', $link->expires_at?->toDateString()) }}" class="input @error('expires_on') input-error @enderror">
                @error('expires_on')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
            <button class="btn btn-primary w-full">Save changes</button>
        </form>
    </aside>
</div>
<script>
    document.querySelectorAll('[data-copy]').forEach(function (b) {
        b.addEventListener('click', function () {
            navigator.clipboard.writeText(b.dataset.copy).then(function () { b.textContent = 'Copied'; setTimeout(function () { b.textContent = 'Copy'; }, 1500); });
        });
    });
</script>
@endsection
