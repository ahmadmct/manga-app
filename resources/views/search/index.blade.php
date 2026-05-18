@extends('layouts.app')

@section('title', $query ? "Search: {$query} — MangAlfa" : 'Search Manga — MangAlfa')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 animate-fade-in">

    <!-- Search Header -->
    <div class="mb-6">
        <h1 class="font-display font-700 text-2xl text-white mb-4">
            @if($query)
                Results for <span class="text-accent-light">"{{ $query }}"</span>
            @else
                Search Manga
            @endif
        </h1>

        <!-- Search Form -->
        <form action="{{ route('search') }}" method="GET" class="relative" id="search-form">
            <input 
                type="text" 
                name="q" 
                id="search-input"
                value="{{ $query }}"
                placeholder="Search by title..."
                autofocus
                autocomplete="off"
                class="w-full bg-dark-800 border border-purple-900/30 focus:border-accent rounded-2xl px-5 py-4 pl-12 text-base text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-accent transition-all"
            >
            <svg class="absolute left-4 top-4.5 w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="top:50%;transform:translateY(-50%)">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            @if($query)
            <a href="{{ route('search') }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
            @endif
        </form>
    </div>

    <!-- Genre Filter (hide when results are shown) -->
    @if(!empty($genres) && (empty($query) || empty($results)))
    <div class="mb-6" id="genre-filter">
        <p class="text-xs text-slate-500 mb-2 font-medium uppercase tracking-wider">Browse by Genre</p>
        <div class="flex flex-wrap gap-2">
            @foreach(array_slice($genres, 0, 24) as $genre)
            @php
                $genreId = $genre['genre_id'] ?? $genre['name'] ?? $genre;
                $genreName = $genre['genre_name'] ?? $genre['name'] ?? $genre;
            @endphp
            <a href="{{ route('manga.genre', $genreId) }}"
               class="px-3 py-1.5 bg-dark-700 hover:bg-accent/20 border border-dark-500 hover:border-accent/40 rounded-full text-xs text-slate-300 hover:text-accent-light transition-all">
                {{ $genreName }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Results -->
    @if($query)
        @if(!empty($results))
        <div class="mb-3 flex items-center justify-between">
            <p class="text-sm text-slate-400"><span class="text-white font-medium">{{ count($results) }}</span> results found</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="results-grid">
            @foreach($results as $manga)
            @php
                $slug = trim(($manga['endpoint'] ?? $manga['manga_endpoint'] ?? Str::slug($manga['title'] ?? 'manga')), '/');
            @endphp
            <a href="{{ route('manga.show', $slug) }}" class="manga-card group block animate-slide-up">
                <div class="relative rounded-xl overflow-hidden bg-dark-800 aspect-[2/3] mb-2">
                    <img 
                        src="{{ $manga['thumb'] ?? $manga['image'] ?? '' }}" 
                        alt="{{ $manga['title'] ?? '' }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                        onerror="this.parentElement.innerHTML='<div class=\'w-full h-full bg-dark-700 flex items-center justify-center\'><svg class=\'w-8 h-8 text-slate-600\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg></div>'"
                    >
                    @if(!empty($manga['type']))
                    <div class="absolute top-2 right-2">
                        <span class="text-xs px-1.5 py-0.5 bg-black/60 rounded text-slate-300 backdrop-blur-sm">{{ $manga['type'] }}</span>
                    </div>
                    @endif
                    @if(!empty($manga['status']))
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent px-2 pb-1.5 pt-4">
                        <span class="text-xs {{ strtolower($manga['status']) === 'ongoing' ? 'text-green-400' : 'text-blue-400' }}">{{ $manga['status'] }}</span>
                    </div>
                    @endif
                </div>
                <h3 class="text-xs font-medium text-slate-300 group-hover:text-white line-clamp-2 transition-colors">{{ $manga['title'] ?? 'Unknown Title' }}</h3>
                @if(!empty($manga['chapter']))
                <p class="text-xs text-accent-light mt-0.5">{{ $manga['chapter'] }}</p>
                @endif
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-dark-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">No results found</h3>
            <p class="text-slate-500 text-sm">Try a different keyword or browse by genre below.</p>
        </div>
        @endif
    @else
    <!-- Empty state with suggestions -->
    <div class="text-center py-12">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-w-sm mx-auto mb-6">
            @foreach(['One Piece', 'Naruto', 'Attack on Titan', 'Demon Slayer', 'My Hero Academia', 'Jujutsu Kaisen'] as $suggestion)
            <button onclick="document.getElementById('search-input').value='{{ $suggestion }}'; document.getElementById('search-form').submit();"
                    class="px-3 py-2 bg-dark-800 hover:bg-dark-700 border border-dark-600 hover:border-accent/40 rounded-xl text-xs text-slate-400 hover:text-accent-light transition-all text-left">
                {{ $suggestion }}
            </button>
            @endforeach
        </div>
        <p class="text-xs text-slate-600">Popular searches</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Live search with debounce
    let debounceTimer;
    const searchInput = document.getElementById('search-input');
    
    searchInput?.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) return;
        
        debounceTimer = setTimeout(() => {
            fetch(`{{ route('search') }}?q=${encodeURIComponent(query)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                updateResults(data.results, query);
            });
        }, 400);
    });

    function updateResults(results, query) {
        const grid = document.getElementById('results-grid');
        const genreFilter = document.getElementById('genre-filter');
        if (!grid || !results) return;
        
        if (results.length === 0) return;

        if (genreFilter) genreFilter.style.display = 'none';
        
        grid.innerHTML = results.slice(0, 20).map(manga => {
            const rawSlug = manga.endpoint || manga.manga_endpoint || manga.title?.toLowerCase().replace(/[^a-z0-9]+/g, '-') || 'manga';
            const slug = rawSlug.replace(/^\/+|\/+$/g, '');
            const thumb = manga.thumb || manga.image || '';
            const title = manga.title || 'Unknown';
            const chapter = manga.chapter || '';
            return `
                <a href="/manga/${slug}" class="manga-card group block animate-slide-up">
                    <div class="relative rounded-xl overflow-hidden bg-dark-800 aspect-[2/3] mb-2">
                        <img src="${thumb}" alt="${title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                    </div>
                    <h3 class="text-xs font-medium text-slate-300 group-hover:text-white line-clamp-2 transition-colors">${title}</h3>
                    ${chapter ? `<p class="text-xs text-accent-light mt-0.5">${chapter}</p>` : ''}
                </a>
            `;
        }).join('');
    }
</script>
@endpush
