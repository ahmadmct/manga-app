@extends('layouts.app')

@section('title', 'Reading History — MangAlfa')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 animate-fade-in">

    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display font-700 text-2xl text-white flex items-center gap-2">
            <svg class="w-6 h-6 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Reading History
        </h1>
        @if(!empty($history))
        <button onclick="clearHistory()" class="text-xs text-slate-500 hover:text-red-400 transition-colors px-3 py-1.5 border border-dark-600 hover:border-red-900/50 rounded-xl">
            Clear All
        </button>
        @endif
    </div>

    @if(!empty($history))
    <div class="space-y-2" id="history-list">
        @foreach($history as $item)
        <div class="group flex items-center gap-3 bg-dark-800/50 hover:bg-dark-700 border border-transparent hover:border-purple-900/30 rounded-xl p-3 transition-all" id="history-item-{{ $loop->index }}">
            <!-- Icon -->
            <div class="flex-shrink-0 w-10 h-10 bg-dark-700 rounded-xl flex items-center justify-center border border-dark-600 group-hover:border-accent/30 transition-colors">
                <svg class="w-5 h-5 text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <a href="{{ route('chapter.show', $item['chapter_slug']) }}" class="block">
                    <p class="text-sm font-medium text-white truncate hover:text-accent-light transition-colors">
                        {{ $item['chapter_title'] ?: $item['chapter_slug'] }}
                    </p>
                    <p class="text-xs text-slate-500 truncate">{{ $item['manga_slug'] ?? '' }}</p>
                </a>
            </div>

            <!-- Time + actions -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="text-right hidden sm:block">
                    <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($item['read_at'])->diffForHumans() }}</p>
                    <p class="text-xs text-slate-700">{{ \Carbon\Carbon::parse($item['read_at'])->format('M d') }}</p>
                </div>
                <a href="{{ route('chapter.show', $item['chapter_slug']) }}"
                   class="w-8 h-8 rounded-lg bg-accent/10 hover:bg-accent/20 border border-accent/20 flex items-center justify-center text-accent-light transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    </svg>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-20 h-20 bg-dark-800 rounded-2xl flex items-center justify-center mb-5 border border-dark-600">
            <svg class="w-10 h-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-white mb-2">No reading history</h2>
        <p class="text-slate-500 text-sm mb-6 max-w-xs">Start reading manga and your history will show up here automatically.</p>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-accent hover:bg-accent-dark text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all hover:shadow-lg hover:shadow-accent/30">
            Discover Manga
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function clearHistory() {
    if (!confirm('Clear your entire reading history?')) return;
    fetch('{{ route("history.add", [], false) }}', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ clear_all: true })
    }).then(() => {
        document.getElementById('history-list')?.remove();
        location.reload();
    });
}
</script>
@endpush
