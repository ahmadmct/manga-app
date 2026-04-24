@extends('layouts.app')

@section('title', 'MangAlfa — Read Manga Online Free')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 animate-fade-in">

    <!-- Hero / Search Banner -->
    <section class="relative rounded-2xl overflow-hidden mb-8 bg-gradient-to-br from-dark-800 via-dark-700 to-dark-800 border border-purple-900/20">
        <div class="absolute inset-0 bg-gradient-radial from-accent/10 via-transparent to-transparent"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-accent/5 rounded-full blur-3xl"></div>
        <div class="relative z-10 px-6 py-8 md:py-12 md:px-12">
            <div class="inline-flex items-center gap-2 bg-accent/10 border border-accent/20 rounded-full px-3 py-1 mb-4">
                <div class="w-1.5 h-1.5 bg-accent-light rounded-full animate-pulse-slow"></div>
                <span class="text-xs text-accent-light font-medium">Updated Daily</span>
            </div>
            <h1 class="font-display text-3xl md:text-4xl font-800 text-white mb-2">
                Your Manga Universe,<br>
                <span class="text-accent-light glow-text">Unlocked.</span>
            </h1>
            <p class="text-slate-400 text-sm md:text-base mb-6 max-w-md">Thousands of manga chapters, beautifully rendered. Read anywhere, anytime.</p>
            <form action="{{ route('search') }}" method="GET" class="flex gap-2 max-w-lg">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Search title, author..."
                        class="w-full bg-dark-950/60 border border-purple-900/30 rounded-xl px-4 py-3 pl-11 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all"
                    >
                    <svg class="absolute left-4 top-3.5 w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button type="submit" class="bg-accent hover:bg-accent-dark text-white px-5 py-3 rounded-xl text-sm font-semibold transition-all hover:shadow-lg hover:shadow-accent/30">Search</button>
            </form>
        </div>
    </section>

    <!-- Continue Reading (if history exists) -->
    @if(!empty($readingHistory))
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-700 text-lg text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Continue Reading
            </h2>
            <a href="{{ route('history.index') }}" class="text-xs text-accent-light hover:text-accent">View All →</a>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-none" style="scrollbar-width:none;-ms-overflow-style:none;">
            @foreach(array_slice($readingHistory, 0, 8) as $item)
            <a href="{{ route('chapter.show', $item['chapter_slug']) }}" 
               class="flex-shrink-0 bg-dark-800 border border-purple-900/20 rounded-xl p-3 flex items-center gap-3 hover:border-accent/40 transition-all min-w-48 max-w-56">
                <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-white font-medium truncate">{{ $item['manga_slug'] ?? 'Manga' }}</p>
                    <p class="text-xs text-accent-light truncate">{{ $item['chapter_title'] ?? $item['chapter_slug'] }}</p>
                    <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($item['read_at'])->diffForHumans() }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Genre Pills -->
    @if(!empty($genres))
    <section class="mb-8">
        <div class="flex items-center gap-2 mb-4 flex-wrap">
            <span class="text-sm text-slate-500 font-medium">Genres:</span>
            @foreach(array_slice($genres, 0, 16) as $genre)
            <a href="{{ route('manga.genre', $genre['genre_id'] ?? $genre['name'] ?? $genre) }}" 
               class="px-3 py-1 bg-dark-700 hover:bg-accent/20 border border-dark-500 hover:border-accent/40 rounded-full text-xs text-slate-300 hover:text-accent-light transition-all">
                {{ $genre['genre_name'] ?? $genre['name'] ?? $genre }}
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Recommended -->
    @if(!empty($recommendedManga))
    <section class="mb-10">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display font-700 text-xl text-white flex items-center gap-2">
                <span class="text-accent-light">✨</span> Recommended
            </h2>
            <a href="{{ route('recommended.index') }}" class="text-xs text-accent-light hover:text-accent">View All →</a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach(array_slice($recommendedManga, 0, 12) as $manga)
            @php
                $slug = trim(($manga['endpoint'] ?? $manga['manga_endpoint'] ?? Str::slug($manga['title'] ?? 'manga')), '/');
            @endphp
            <a href="{{ route('manga.show', $slug) }}" class="manga-card group block">
                <div class="relative rounded-xl overflow-hidden bg-dark-800 aspect-[2/3] mb-2">
                    <img 
                        src="{{ $manga['thumb'] ?? $manga['image'] ?? '' }}" 
                        alt="{{ $manga['title'] ?? 'Manga' }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                        onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 150%22><rect fill=%22%231a1a2e%22 width=%22100%22 height=%22150%22/></svg>'"
                    >
                    @if(!empty($manga['chapter']))
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent px-2 pt-6 pb-1">
                        <p class="text-xs text-accent-light truncate">{{ $manga['chapter'] }}</p>
                    </div>
                    @endif
                </div>
                <h3 class="text-xs font-medium text-slate-300 group-hover:text-white line-clamp-2 transition-colors">{{ $manga['title'] ?? 'Unknown' }}</h3>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Popular Manga -->
    @if(!empty($popularManga))
    <section class="mb-10">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display font-700 text-xl text-white flex items-center gap-2">
                <span class="text-accent-light">🔥</span> Popular Manga
            </h2>
            <a href="{{ route('manga.index') }}" class="text-xs text-accent-light hover:text-accent">View All →</a>
        </div>

        <!-- Featured Popular (first one big) -->
        @if(count($popularManga) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach(array_slice($popularManga, 0, 12) as $index => $manga)
            @php
                $slug = trim(($manga['endpoint'] ?? $manga['manga_endpoint'] ?? Str::slug($manga['title'] ?? 'manga')), '/');
            @endphp
            <a href="{{ route('manga.show', $slug) }}" 
               class="manga-card group block">
                <div class="relative rounded-xl overflow-hidden bg-dark-800 aspect-[2/3] mb-2">
                    <img 
                        src="{{ $manga['thumb'] ?? $manga['image'] ?? '/images/placeholder.jpg' }}" 
                        alt="{{ $manga['title'] ?? 'Manga Cover' }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                        onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 150%22><rect fill=%22%231a1a2e%22 width=%22100%22 height=%22150%22/><text x=%2250%22 y=%2280%22 text-anchor=%22middle%22 fill=%22%237c3aed%22 font-size=%2212%22>No Image</text></svg>'"
                    >
                    @if($index < 3)
                    <div class="absolute top-2 left-2 w-6 h-6 bg-accent rounded-lg flex items-center justify-center text-xs font-800 text-white">{{ $index + 1 }}</div>
                    @endif
                    @if(!empty($manga['chapter']))
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent px-2 pt-6 pb-1">
                        <p class="text-xs text-accent-light truncate">{{ $manga['chapter'] }}</p>
                    </div>
                    @endif
                    <div class="absolute inset-0 bg-accent/0 group-hover:bg-accent/5 transition-colors"></div>
                </div>
                <h3 class="text-xs font-medium text-slate-300 group-hover:text-white line-clamp-2 transition-colors">{{ $manga['title'] ?? 'Unknown Title' }}</h3>
                @if(!empty($manga['type']))
                <span class="text-xs text-slate-500">{{ $manga['type'] }}</span>
                @endif
            </a>
            @endforeach
        </div>
        @endif
    </section>
    @endif

    <!-- Latest Updates -->
    @if(!empty($latestManga))
    <section class="mb-10">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display font-700 text-xl text-white flex items-center gap-2">
                <span class="text-accent-light">⚡</span> Latest Updates
            </h2>
            <a href="{{ route('manga.index') }}" class="text-xs text-accent-light hover:text-accent">View All →</a>
        </div>

        <div class="space-y-2">
            @foreach(array_slice($latestManga, 0, 15) as $manga)
            @php
                $slug = trim(($manga['endpoint'] ?? $manga['manga_endpoint'] ?? Str::slug($manga['title'] ?? 'manga')), '/');
            @endphp
            <a href="{{ route('manga.show', $slug) }}" 
               class="group flex items-center gap-3 bg-dark-800/50 hover:bg-dark-700 border border-transparent hover:border-purple-900/30 rounded-xl p-3 transition-all">
                <div class="relative flex-shrink-0 w-12 h-16 rounded-lg overflow-hidden bg-dark-700">
                    <img 
                        src="{{ $manga['thumb'] ?? $manga['image'] ?? '' }}" 
                        alt="{{ $manga['title'] ?? '' }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                        onerror="this.parentElement.innerHTML='<div class=\'w-full h-full bg-dark-600 flex items-center justify-center\'><svg class=\'w-5 h-5 text-slate-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13\'/></svg></div>'"
                    >
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-white group-hover:text-accent-light line-clamp-1 transition-colors">{{ $manga['title'] ?? 'Unknown' }}</h3>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        @if(!empty($manga['chapter']))
                        <span class="badge-new text-white">{{ $manga['chapter'] }}</span>
                        @endif
                        @if(!empty($manga['type']))
                        <span class="text-xs text-slate-500 bg-dark-600 px-2 py-0.5 rounded">{{ $manga['type'] }}</span>
                        @endif
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-600 group-hover:text-accent-light flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Bookmarks Preview -->
    @if(!empty($bookmarks))
    <section class="mb-10">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display font-700 text-xl text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-accent-light" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                My Bookmarks
            </h2>
            <a href="{{ route('bookmarks.index') }}" class="text-xs text-accent-light hover:text-accent">View All →</a>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
            @foreach(array_slice($bookmarks, 0, 6) as $bookmark)
            <a href="{{ route('manga.show', $bookmark['slug']) }}" class="manga-card group block">
                <div class="rounded-xl overflow-hidden bg-dark-800 aspect-[2/3] mb-1">
                    <img src="{{ $bookmark['thumb'] }}" alt="{{ $bookmark['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy">
                </div>
                <p class="text-xs text-slate-400 line-clamp-2 group-hover:text-white transition-colors">{{ $bookmark['title'] }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

</div>

<!-- API Error State -->
@if(empty($latestManga) && empty($popularManga))
<div class="max-w-md mx-auto text-center py-20 px-4">
    <div class="w-16 h-16 bg-dark-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-white mb-2">Content Unavailable</h3>
    <p class="text-slate-500 text-sm mb-4">The manga API might be temporarily down. Please try again later.</p>
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-accent text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-accent-dark transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Retry
    </a>
</div>
@endif
@endsection
