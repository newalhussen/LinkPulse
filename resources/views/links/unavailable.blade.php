@extends('layouts.app')
@section('title', 'Link unavailable')
@section('content')
<div class="card mx-auto mt-10 max-w-md text-center">
    <h1 class="text-xl font-semibold">This link is no longer available</h1>
    <p class="mt-2 text-sm text-slate-600">
        @if ($link->isExpired()) It expired and no longer redirects. @else Its owner has turned it off. @endif
        If you expected it to work, contact whoever shared it with you.
    </p>
</div>
@endsection
