@extends('layouts.app')
@section('title', 'Short links with analytics')
@section('content')
<section class="mx-auto max-w-2xl py-10 text-center">
    <h1 class="text-4xl font-bold tracking-tight">Short links you can actually measure.</h1>
    <p class="mt-3 text-slate-600">Create short URLs and custom aliases, set expiry dates, download QR codes, and see clicks, referrers and devices. Visitors are never tracked as individuals: no IP addresses, no cookies.</p>
    <div class="mt-6 flex justify-center gap-3">
        <a href="{{ route('register') }}" class="btn btn-primary">Create a free account</a>
        <a href="{{ route('login') }}" class="btn">Sign in</a>
    </div>
</section>
<section class="grid gap-4 sm:grid-cols-3">
    <div class="card"><h2 class="font-semibold">Aliases and expiry</h2><p class="mt-1 text-sm text-slate-600">Pick a memorable alias or get a random code. Links can expire on a date or be switched off at any time.</p></div>
    <div class="card"><h2 class="font-semibold">QR codes</h2><p class="mt-1 text-sm text-slate-600">Every link has a scalable SVG QR code you can download for print.</p></div>
    <div class="card"><h2 class="font-semibold">Privacy-first analytics</h2><p class="mt-1 text-sm text-slate-600">Clicks over time, referring sites and device types. Bots and link previews are not counted.</p></div>
</section>
@endsection
