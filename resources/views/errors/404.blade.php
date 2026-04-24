@extends('layouts.app')

@section('title', '404 — Page Not Found — MangAlfa')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center animate-fade-in">
        <!-- Glowing 404 -->
        <div class="relative inline-block mb-6">
            <span class="font-display font-800 text-8xl md:text-9xl text-dark-700 select-none">404</span>
            <span class="font-display font-800 text-8xl md:text-9xl text-accent-light absolute inset-0 blur-2xl opacity-20 select-none">404</span>
        </div>

        <h1 class="font-display font-700 text-2xl text-white mb-3">Chapter Not Found</h1>
        <p class="text-slate-500 text-sm mb-8 max-w-sm mx-auto">
            The manga or chapter you're looking for doesn't exist or may have been removed.
        </p>

        <div class="flex items-center justify-center gap-3 flex-wrap">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-accent hover:bg-accent-dark text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all hover:shadow-lg hover:shadow-accent/30">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Go Home
            </a>
            <a href="{{ route('search') }}" class="inline-flex items-center gap-2 bg-dark-800 hover:bg-dark-700 border border-dark-600 text-slate-300 hover:text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search Manga
            </a>
        </div>
    </div>
</div>
@endsection
