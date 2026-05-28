@extends('layouts.app')

@section('title', 'Login - MangAlfa')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-16 animate-fade-in">
    <div class="bg-dark-800/60 border border-dark-600 rounded-2xl p-6">
        <h1 class="font-display font-700 text-2xl text-white mb-6">Login</h1>

        @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-slate-400 mb-1" for="login">Nama atau Email</label>
                <input id="login" name="login" value="{{ old('login') }}" required autofocus
                       class="w-full bg-dark-700 border border-dark-500 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-accent">
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" required
                       class="w-full bg-dark-700 border border-dark-500 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-accent">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" name="remember" value="1" class="rounded border-dark-500 bg-dark-700 text-accent">
                Remember me
            </label>

            <button class="w-full bg-accent hover:bg-accent-dark text-white py-2.5 rounded-xl text-sm font-semibold transition-all">
                Login
            </button>
        </form>
    </div>
</div>
@endsection
