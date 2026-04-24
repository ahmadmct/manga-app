@extends('layouts.app')

@section('title', ucfirst($genre) . ' Manga — MangAlfa')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 animate-fade-in">
    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
        <a href="{{ route('home') }}" class="text-slate-500 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="font-display font-700 text-2xl text-white capitalize">{{ str_replace('-', ' ', $genre) }}</h1>
        <span class="badge-new text-white text-xs px-2 py-1">Genre</span>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="page" value="1">
            <label for="per_page" class="text-xs text-slate-500">Per page</label>
            <select id="per_page" name="per_page" onchange="this.form.submit()"
                class="bg-dark-800 border border-dark-600 rounded-xl px-2.5 py-2 text-sm text-slate-200 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                @foreach([6, 12, 24, 36, 48, 60] as $opt)
                    <option value="{{ $opt }}" {{ ($perPage ?? 24) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Other Genres -->
    @if(!empty($genres))
    <div class="flex gap-2 overflow-x-auto pb-3 mb-6 scrollbar-none" style="scrollbar-width:none;">
        @foreach(array_slice($genres, 0, 20) as $g)
        @php $gId = $g['genre_id'] ?? $g['name'] ?? $g; $gName = $g['genre_name'] ?? $g['name'] ?? $g; @endphp
        <a href="{{ route('manga.genre', $gId) }}" 
           class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs border transition-all
           {{ $gId == $genre ? 'bg-accent/20 border-accent/50 text-accent-light' : 'bg-dark-700 border-dark-500 text-slate-400 hover:text-white hover:border-dark-400' }}">
            {{ $gName }}
        </a>
        @endforeach
    </div>
    @endif

    @if(!empty($manga))
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 md:gap-4 mb-8">
        @foreach($manga as $item)
        @php $slug = trim(($item['endpoint'] ?? $item['manga_endpoint'] ?? Str::slug($item['title'] ?? 'manga')), '/'); @endphp
        <a href="{{ route('manga.show', $slug) }}" class="manga-card group block">
            <div class="relative rounded-xl overflow-hidden bg-dark-800 aspect-[2/3] mb-2">
                <img src="{{ $item['thumb'] ?? $item['image'] ?? '' }}" alt="{{ $item['title'] ?? '' }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy"
                    onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 150%22><rect fill=%22%231a1a2e%22 width=%22100%22 height=%22150%22/></svg>'">
                @if(!empty($item['chapter']))
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent px-1.5 pb-1 pt-4">
                    <p class="text-[10px] text-accent-light truncate">{{ $item['chapter'] }}</p>
                </div>
                @endif
            </div>
            <h3 class="text-xs font-medium text-slate-300 group-hover:text-white line-clamp-2 transition-colors">{{ $item['title'] ?? 'Unknown' }}</h3>
        </a>
        @endforeach
    </div>

    <div class="flex items-center justify-center gap-2">
        @if($page > 1)
        <a href="{{ route('manga.genre', [$genre, 'page' => $page - 1, 'per_page' => ($perPage ?? 24)]) }}" class="px-4 py-2 bg-dark-800 hover:bg-dark-700 border border-dark-600 rounded-xl text-sm text-slate-300 hover:text-white transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Previous
        </a>
        @endif
        <span class="px-4 py-2 bg-accent/20 border border-accent/30 rounded-xl text-sm text-accent-light">{{ $page }}</span>
        <a href="{{ route('manga.genre', [$genre, 'page' => $page + 1, 'per_page' => ($perPage ?? 24)]) }}" class="px-4 py-2 bg-dark-800 hover:bg-dark-700 border border-dark-600 rounded-xl text-sm text-slate-300 hover:text-white transition-all flex items-center gap-2">
            Next
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    @else
    <div class="text-center py-20">
        <p class="text-slate-500 text-sm">No manga found for this genre.</p>
        <a href="{{ route('home') }}" class="mt-4 inline-flex text-accent-light text-sm hover:text-accent">← Back Home</a>
    </div>
    @endif
</div>
@endsection
