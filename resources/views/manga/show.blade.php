@extends('layouts.app')

@section('title', ($manga['title'] ?? 'Manga') . ' — MangAlfa')
@section('description', Str::limit($manga['synopsis'] ?? $manga['description'] ?? '', 160))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 animate-slide-up">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-accent-light transition-colors">Home</a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('manga.index') }}" class="hover:text-accent-light transition-colors">Browse</a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-300 truncate max-w-32">{{ $manga['title'] ?? 'Manga' }}</span>
    </nav>

    <!-- Manga Header -->
    <div class="flex gap-5 mb-8">
        <!-- Cover -->
        <div class="flex-shrink-0 relative">
            <div class="w-28 sm:w-36 md:w-44 rounded-2xl overflow-hidden bg-dark-800 shadow-2xl shadow-black/50">
                <img 
                    src="{{ $manga['thumb'] ?? $manga['image'] ?? '' }}" 
                    alt="{{ $manga['title'] ?? 'Cover' }}"
                    class="w-full aspect-[2/3] object-cover"
                    onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 150%22><rect fill=%22%231a1a2e%22 width=%22100%22 height=%22150%22/></svg>'"
                >
            </div>
            <!-- Status badge -->
            @if(!empty($manga['status']))
            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 whitespace-nowrap">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                    {{ strtolower($manga['status']) === 'ongoing' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                    {{ $manga['status'] }}
                </span>
            </div>
            @endif
        </div>

        <!-- Info -->
        <div class="flex-1 min-w-0 pt-1">
            <h1 class="font-display font-700 text-xl md:text-2xl text-white leading-tight mb-1">{{ $manga['title'] ?? 'Unknown Title' }}</h1>

            @if(!empty($manga['author']))
            <p class="text-sm text-accent-light mb-3 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ $manga['author'] }}
            </p>
            @endif

            <!-- Genres -->
            @if(!empty($manga['genres']) || !empty($manga['genre_list']))
            <div class="flex flex-wrap gap-1.5 mb-3">
                @foreach(($manga['genres'] ?? $manga['genre_list'] ?? []) as $genre)
                {{-- @dd($manga) --}}
                <a href="{{ route('manga.genre', $genre['genre_id'] ?? $genre['genre_name'] ?? $genre) }}" 
                   class="text-xs px-2.5 py-1 bg-dark-700 hover:bg-accent/20 border border-dark-500 hover:border-accent/40 rounded-full text-slate-300 hover:text-accent-light transition-all">
                    {{ $genre['genre_name'] ?? $genre['name'] ?? $genre }}
                </a>
                @endforeach
            </div>
            @endif

            <!-- Stats row -->
            <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
                @if(!empty($manga['type']))
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    {{ $manga['type'] }}
                </span>
                @endif
                @if(!empty($chapters))
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ count($chapters) }} Chapters
                </span>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                @if(!empty($chapters))
                @php
                    $firstChapter = end($chapters);
                    $lastChapter = reset($chapters);
                    $firstSlug = $firstChapter['chapter_endpoint'] ?? $firstChapter['endpoint'] ?? '';
                    $lastSlug = $lastChapter['chapter_endpoint'] ?? $lastChapter['endpoint'] ?? '';
                @endphp
                <a href="{{ route('chapter.show', $firstSlug) }}" 
                   class="flex-1 bg-accent hover:bg-accent-dark text-white py-2.5 rounded-xl text-sm font-semibold text-center transition-all hover:shadow-lg hover:shadow-accent/30 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                    Start Reading
                </a>
                @endif

                <!-- Bookmark Button -->
                <button 
                    id="bookmark-btn"
                    data-slug="{{ $slug }}"
                    data-title="{{ $manga['title'] ?? '' }}"
                    data-thumb="{{ $manga['thumb'] ?? $manga['image'] ?? '' }}"
                    data-type="{{ $manga['type'] ?? 'Manga' }}"
                    onclick="toggleBookmark(this)"
                    class="w-11 h-11 rounded-xl border {{ $isBookmarked ? 'bg-accent/20 border-accent/50 text-accent-light' : 'bg-dark-700 border-dark-500 text-slate-400 hover:text-accent-light hover:border-accent/40' }} flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="{{ $isBookmarked ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" id="bookmark-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Synopsis -->
    @if(!empty($manga['synopsis']) || !empty($manga['description']))
    <div class="bg-dark-800/50 border border-dark-600 rounded-2xl p-5 mb-6">
        <h2 class="font-display font-600 text-sm text-slate-400 uppercase tracking-widest mb-3">Synopsis</h2>
        <div x-data="{ expanded: false }">
            <p class="text-sm text-slate-300 leading-relaxed" id="synopsis-text" style="display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $manga['synopsis'] ?? $manga['description'] ?? 'No synopsis available.' }}
            </p>
            <button onclick="toggleSynopsis(this)" class="text-xs text-accent-light mt-2 hover:text-accent" data-expanded="false">Read more ↓</button>
        </div>
    </div>
    @endif

    <!-- Chapter List -->
    @if(!empty($chapters))
    <div class="bg-dark-800/50 border border-dark-600 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-dark-600">
            <h2 class="font-display font-600 text-base text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Chapters <span class="text-sm text-slate-500 font-normal">({{ count($chapters) }})</span>
            </h2>
            <div class="flex items-center gap-2 text-xs">
                <button onclick="sortChapters('asc')" class="px-2 py-1 bg-dark-700 rounded-lg text-slate-400 hover:text-white transition-colors" id="sort-asc">↑ Oldest</button>
                <button onclick="sortChapters('desc')" class="px-2 py-1 bg-accent/20 border border-accent/30 rounded-lg text-accent-light" id="sort-desc">↓ Newest</button>
            </div>
        </div>

        <div class="divide-y divide-dark-600 max-h-96 overflow-y-auto" id="chapter-list">
            @foreach($chapters as $chapter)
            @php
                $chapterSlug = trim(($chapter['chapter_endpoint'] ?? $chapter['endpoint'] ?? ''), '/');
                $chapterTitle = $chapter['chapter_title'] ?? $chapter['title'] ?? '';
                $chapterDate = $chapter['chapter_date'] ?? $chapter['date'] ?? '';
            @endphp
            @if($chapterSlug)
            <a href="{{ route('chapter.show', $chapterSlug) }}" 
               class="flex items-center justify-between px-5 py-3 hover:bg-dark-700 transition-colors group chapter-item">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-dark-500 group-hover:bg-accent-light transition-colors flex-shrink-0"></div>
                    <span class="text-sm text-slate-300 group-hover:text-white transition-colors">{{ $chapterTitle ?: $chapterSlug }}</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($chapterDate)
                    <span class="text-xs text-slate-600">{{ $chapterDate }}</span>
                    @endif
                    <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-accent-light transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endif
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-dark-800/50 border border-dark-600 rounded-2xl p-8 text-center">
        <p class="text-slate-500 text-sm">No chapters available at this time.</p>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function toggleBookmark(btn) {
    const slug = btn.dataset.slug;
    const title = btn.dataset.title;
    const thumb = btn.dataset.thumb;
    const type = btn.dataset.type;
    const icon = document.getElementById('bookmark-icon');

    fetch('{{ route("bookmarks.toggle") }}', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ slug, title, thumb, type })
    })
    .then(r => r.json())
    .then(data => {
        if (data.bookmarked) {
            btn.classList.add('bg-accent/20', 'border-accent/50', 'text-accent-light');
            btn.classList.remove('bg-dark-700', 'border-dark-500', 'text-slate-400');
            icon.setAttribute('fill', 'currentColor');
        } else {
            btn.classList.remove('bg-accent/20', 'border-accent/50', 'text-accent-light');
            btn.classList.add('bg-dark-700', 'border-dark-500', 'text-slate-400');
            icon.setAttribute('fill', 'none');
        }
    });
}

function toggleSynopsis(btn) {
    const text = document.getElementById('synopsis-text');
    const expanded = btn.dataset.expanded === 'true';
    if (expanded) {
        text.style['-webkit-line-clamp'] = '4';
        text.style.webkitLineClamp = '4';
        text.style.display = '-webkit-box';
        btn.textContent = 'Read more ↓';
        btn.dataset.expanded = 'false';
    } else {
        text.style.display = 'block';
        btn.textContent = 'Read less ↑';
        btn.dataset.expanded = 'true';
    }
}

function sortChapters(order) {
    const list = document.getElementById('chapter-list');
    const items = Array.from(list.querySelectorAll('.chapter-item'));
    items.sort((a, b) => order === 'asc' ? 1 : -1);
    items.forEach(item => list.appendChild(item));
    
    document.getElementById('sort-asc').className = 'px-2 py-1 bg-dark-700 rounded-lg text-slate-400 hover:text-white transition-colors';
    document.getElementById('sort-desc').className = 'px-2 py-1 bg-dark-700 rounded-lg text-slate-400 hover:text-white transition-colors';
    document.getElementById('sort-' + order).className = 'px-2 py-1 bg-accent/20 border border-accent/30 rounded-lg text-accent-light';
}
</script>
@endpush
