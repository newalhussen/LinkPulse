<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'LinkPulse') · LinkPulse</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-5xl items-center gap-4 px-4 py-3">
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center gap-2 font-semibold">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 0 0-7.07-7.07l-1.5 1.5"/><path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 0 0 7.07 7.07l1.5-1.5"/></svg>
            LinkPulse
        </a>
        <nav class="ml-auto flex items-center gap-2 text-sm">
            @auth
                <span class="hidden text-slate-500 sm:inline">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf <button class="btn">Sign out</button></form>
            @else
                <a href="{{ route('login') }}" class="btn">Sign in</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get started</a>
            @endauth
        </nav>
    </div>
</header>
<main class="mx-auto max-w-5xl px-4 py-8">
    @if (session('status'))
        <div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
