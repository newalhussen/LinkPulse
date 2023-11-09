@extends('layouts.app')
@section('title', 'Your links')
@section('content')
@php($badge = ['active' => 'bg-emerald-100 text-emerald-800', 'disabled' => 'bg-slate-200 text-slate-700', 'expired' => 'bg-amber-100 text-amber-800'])
<div class="grid grid-cols-3 gap-3">
    @foreach ([['Links', $totals['links']], ['Active', $totals['active']], ['Total clicks', number_format($totals['clicks'])]] as [$label, $value])
        <div class="card !p-4"><div class="text-xs text-slate-500">{{ $label }}</div><div class="mt-1 text-2xl font-semibold tabular-nums">{{ $value }}</div></div>
    @endforeach
</div>

<form method="POST" action="{{ route('links.store') }}" class="card mt-5 grid gap-3 sm:grid-cols-6" novalidate>
    @csrf
    <h2 class="text-sm font-semibold sm:col-span-6">Create a short link</h2>
    <div class="sm:col-span-6">
        <label for="target_url" class="mb-1 block text-xs font-medium text-slate-600">Destination URL</label>
        <input id="target_url" name="target_url" value="{{ old('target_url') }}" placeholder="https://example.com/a/very/long/address" required class="input @error('target_url') input-error @enderror">
        @error('target_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="code" class="mb-1 block text-xs font-medium text-slate-600">Custom alias (optional)</label>
        <input id="code" name="code" value="{{ old('code') }}" placeholder="spring-sale" class="input @error('code') input-error @enderror">
        @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="title" class="mb-1 block text-xs font-medium text-slate-600">Title (optional)</label>
        <input id="title" name="title" value="{{ old('title') }}" maxlength="120" class="input @error('title') input-error @enderror">
        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-1">
        <label for="expires_on" class="mb-1 block text-xs font-medium text-slate-600">Expires on</label>
        <input id="expires_on" name="expires_on" type="date" value="{{ old('expires_on') }}" class="input @error('expires_on') input-error @enderror">
        @error('expires_on')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-end sm:col-span-1"><button class="btn btn-primary w-full">Shorten</button></div>
</form>

<div class="mt-6 flex items-center gap-3">
    <h2 class="text-lg font-semibold">Your links</h2>
    <form method="GET" action="{{ route('dashboard') }}" class="ml-auto"><input name="q" value="{{ $q }}" placeholder="Search links…" aria-label="Search links" class="input !w-56"></form>
</div>

@if ($links->isEmpty())
    <div class="card mt-3 border-dashed text-center text-sm text-slate-600">
        @if ($q !== '') Nothing matches “{{ $q }}”. <a class="text-brand-600 underline" href="{{ route('dashboard') }}">Clear search</a>
        @else No links yet. Paste a URL above to create your first short link. @endif
    </div>
@else
    <ul class="card mt-3 divide-y divide-slate-100 !p-0">
        @foreach ($links as $link)
            <li class="flex flex-wrap items-center gap-x-4 gap-y-1 px-4 py-3">
                <div class="min-w-0 flex-1">
                    <a href="{{ route('links.show', $link) }}" class="font-medium text-brand-600 hover:underline">{{ $link->title ?: parse_url($link->target_url, PHP_URL_HOST) }}</a>
                    <div class="truncate font-mono text-xs text-slate-500">{{ preg_replace('#^https?://#', '', $link->shortUrl()) }} → {{ \Illuminate\Support\Str::limit($link->target_url, 70) }}</div>
                </div>
                <span class="badge {{ $badge[$link->status()] }}">{{ ucfirst($link->status()) }}</span>
                <span class="w-20 text-right text-sm tabular-nums">{{ number_format($link->clicks_count) }} <span class="text-xs text-slate-500">clicks</span></span>
                <a href="{{ route('links.show', $link) }}" class="btn !py-1">Manage</a>
            </li>
        @endforeach
    </ul>
    <div class="mt-4">{{ $links->links() }}</div>
@endif
@endsection
