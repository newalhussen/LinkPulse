@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
<form method="POST" action="{{ url('/login') }}" class="card mx-auto max-w-sm space-y-4">
    @csrf
    <h1 class="text-xl font-semibold">Sign in</h1>
    <div>
        <label for="email" class="mb-1 block text-sm font-medium">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') input-error @enderror">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="mb-1 block text-sm font-medium">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
    <button class="btn btn-primary w-full">Sign in</button>
    <p class="text-center text-sm text-slate-600">New here? <a class="text-brand-600 underline" href="{{ route('register') }}">Create an account</a></p>
</form>
@endsection
