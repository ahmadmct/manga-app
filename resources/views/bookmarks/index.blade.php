@extends('layouts.app')

@section('title', 'My Bookmarks — MangAlfa')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 animate-fade-in">

    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display font-700 text-2xl text-white flex items-center gap-2">
            <svg class="w-6 h-6 text-accent-light" fill="currentColor" viewBox="0 0 24 24">
                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            My Bookmarks
        </h1>
        @if(!empty($bookmarks))
        <button onclick="clearBookmarks()" class="text-xs text-slate-500 hover:text-red-400 transition-colors px-3 py-1.5 border border-dark-600 hover:border-red-900/50 rounded-xl">
            Clear All
        </button>
        @endif
    </div>

    @if(!empty($bookmarks))
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 md:gap-4" id="bookmarks-grid">
        @foreach($bookmarks as $bookmark)
        <div class="manga-card group relative" id="bookmark-{{ $bookmark['slug'] }}">
            <a href="{{ route('manga.show', $bookmark['slug']) }}" class="block">
                <div class="relative rounded-xl overflow-hidden bg-dark-800 aspect-[2/3] mb-2">
                    <img
                        src="{{ $bookmark['thumb'] ?? '' }}"
                        alt="{{ $bookmark['title'] ?? '' }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        loading="lazy"
                        onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 150%22><rect fill=%22%231a1a2e%22 width=%22100%22 height=%22150%22/></svg>'"
                    >
                    @if(!empty($bookmark['type']))
                    <div class="absolute top-1.5 right-1.5">
                        <span class="text-[10px] px-1.5 py-0.5 bg-black/60 rounded text-slate-300 backdrop-blur-sm">{{ $bookmark['type'] }}</span>
                    </div>
                    @endif
                </div>
            </a>
            <!-- Remove bookmark button -->
            <button
                onclick="removeBookmark('{{ $bookmark['slug'] }}', '{{ addslashes($bookmark['title'] ?? '') }}', '{{ addslashes($bookmark['thumb'] ?? '') }}', '{{ $bookmark['type'] ?? 'Manga' }}')"
                class="absolute top-1.5 left-1.5 w-6 h-6 bg-black/70 hover:bg-red-900/80 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                title="Remove bookmark"
            >
                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <a href="{{ route('manga.show', $bookmark['slug']) }}">
                <h3 class="text-xs font-medium text-slate-300 group-hover:text-white line-clamp-2 transition-colors">{{ $bookmark['title'] ?? 'Unknown' }}</h3>
            </a>
            @if(!empty($bookmark['bookmarked_at']))
            <p class="text-[10px] text-slate-600 mt-0.5">{{ \Carbon\Carbon::parse($bookmark['bookmarked_at'])->diffForHumans() }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-20 h-20 bg-dark-800 rounded-2xl flex items-center justify-center mb-5 border border-dark-600">
            <svg class="w-10 h-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-white mb-2">No bookmarks yet</h2>
        <p class="text-slate-500 text-sm mb-6 max-w-xs">Start bookmarking your favourite manga and they'll appear here for quick access.</p>
        <a href="{{ route('manga.index') }}" class="inline-flex items-center gap-2 bg-accent hover:bg-accent-dark text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all hover:shadow-lg hover:shadow-accent/30">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Browse Manga
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function removeBookmark(slug, title, thumb, type) {
    fetch('{{ route("bookmarks.toggle", [], false) }}', {
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
        if (!data.bookmarked) {
            const el = document.getElementById('bookmark-' + slug);
            if (el) {
                el.style.transition = 'all 0.3s ease';
                el.style.opacity = '0';
                el.style.transform = 'scale(0.8)';
                setTimeout(() => el.remove(), 300);
            }
        }
    });
}

function clearBookmarks() {
    if (!confirm('Clear all bookmarks?')) return;
    const items = document.querySelectorAll('[id^="bookmark-"]');
    items.forEach(el => {
        const slug = el.id.replace('bookmark-', '');
        const img = el.querySelector('img');
        const title = el.querySelector('h3')?.textContent || '';
        const thumb = img?.src || '';
        removeBookmark(slug, title, thumb, 'Manga');
    });
}
</script>
@endpush
