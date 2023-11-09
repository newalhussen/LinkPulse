@extends('layouts.app')
@section('title', 'Create account')
@section('content')
<form method="POST" action="{{ url('/register') }}" class="card mx-auto max-w-sm space-y-4">
    @csrf
    <h1 class="text-xl font-semibold">Create your account</h1>
    @foreach ([['name', 'Name', 'text', 'name'], ['email', 'Email', 'email', 'username']] as [$field, $label, $type, $ac])
        <div>
            <label for="{{ $field }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
            <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" required autocomplete="{{ $ac }}" class="input @error($field) input-error @enderror">
            @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    @endforeach
    <div>
        <label for="password" class="mb-1 block text-sm font-medium">Password <span class="font-normal text-slate-500">(10+ characters)</span></label>
        <input id="password" name="password" type="password" required autocomplete="new-password" class="input @error('password') input-error @enderror">
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium">Repeat password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
    </div>
    <button class="btn btn-primary w-full">Create account</button>
    <p class="text-center text-sm text-slate-600">Already registered? <a class="text-brand-600 underline" href="{{ route('login') }}">Sign in</a></p>
</form>
@endsection
