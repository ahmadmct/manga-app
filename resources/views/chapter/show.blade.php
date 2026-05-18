@extends('layouts.app')

@section('title', ($chapterTitle ?: $chapterSlug) . ' — MangAlfa Reader')

@push('head')
<style>
    body { overflow-x: hidden; }
    .reader-image { 
        display: block; 
        width: 100%; 
        max-width: 800px;
        margin: 0 auto;
        user-select: none;
        -webkit-user-drag: none;
        pointer-events: none;
    }

    /* Controls hidden state */
    .reader-controls,
    .reader-top-bar {
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    .reader-top-bar.hidden-bar {
        opacity: 0;
        pointer-events: none;
        transform: translateY(-100%);
    }
    .reader-controls.hidden-bar {
        opacity: 0;
        pointer-events: none;
        transform: translateY(100%);
    }
    
    .progress-bar { 
        position: fixed; 
        top: 0; left: 0; 
        height: 3px; 
        background: linear-gradient(90deg, #7c3aed, #4a9eff);
        transition: width 0.1s ease;
        z-index: 100;
        box-shadow: 0 0 8px rgba(124, 58, 237, 0.8);
    }

    .img-skeleton {
        background: linear-gradient(90deg, #1a1a2e 25%, #22223b 50%, #1a1a2e 75%);
        background-size: 1000px 100%;
        animation: shimmer 1.5s linear infinite;
        min-height: 400px;
        display: block;
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
    }

    .lazy-img { opacity: 0; transition: opacity 0.3s ease; }
    .lazy-img.loaded { opacity: 1; }

    .reader-container { touch-action: manipulation; -webkit-tap-highlight-color: transparent; }
    .reader-controls { padding-bottom: calc(0.75rem + env(safe-area-inset-bottom)); }

    @media (max-width: 768px) {
        .reader-image { max-width: 100%; }
    }
</style>
@endpush

@section('content')

<!-- Reading Progress Bar -->
<div class="progress-bar" id="progress-bar" style="width: 0%"></div>

<!-- Top Bar -->
<div class="reader-top-bar glass fixed top-0 left-0 right-0 z-40 border-b border-purple-900/20 px-4 py-3" id="reader-top-bar" style="margin-top: 56px;">
    <div class="max-w-3xl mx-auto flex items-center justify-between">
        <a href="{{ route('manga.show', $mangaSlug) }}" class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span class="text-sm truncate max-w-40">{{ $mangaSlug }}</span>
        </a>
        <div class="text-center">
            <p class="text-xs font-medium text-white truncate max-w-48">{{ $chapterTitle ?: $chapterSlug }}</p>
            <p class="text-xs text-slate-500" id="page-indicator">Page <span id="current-page">0</span> / {{ count($images) }}</p>
        </div>
        <button onclick="toggleFullscreen()" class="text-slate-400 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
            </svg>
        </button>
    </div>
</div>

<!-- Reader Container -->
<div class="reader-container min-h-screen pt-28 pb-[calc(7rem+env(safe-area-inset-bottom))] bg-dark-950" id="reader-container" onclick="handleReaderClick(event)">
    
    @if(!empty($images))
    <div id="pages-container" class="space-y-0">
        @foreach($images as $index => $image)
        @php
            $imgUrl = $image['chapter_image_link'] ?? $image['image_link'] ?? $image['url'] ?? $image['link'] ?? (is_string($image) ? $image : '');
            $imgPage = $image['chapter_image_page'] ?? $image['page'] ?? ($index + 1);
        @endphp
        <div class="page-wrapper relative" data-page="{{ $imgPage }}" data-index="{{ $index }}">
            <div class="img-skeleton" id="skeleton-{{ $index }}"></div>
            <img 
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                data-src="{{ $imgUrl }}"
                alt="Page {{ $imgPage }}"
                class="reader-image lazy-img"
                id="img-{{ $index }}"
                loading="lazy"
                onload="imageLoaded({{ $index }})"
                onerror="imageError({{ $index }})"
            >
        </div>
        @endforeach
    </div>
    @else
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <div class="w-16 h-16 bg-dark-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-slate-400 text-sm">No pages found for this chapter.</p>
            <a href="{{ route('manga.show', $mangaSlug) }}" class="mt-4 inline-flex items-center gap-2 text-accent-light text-sm hover:text-accent">← Back to manga</a>
        </div>
    </div>
    @endif
</div>

<!-- Bottom Controls -->
<div class="reader-controls glass fixed bottom-0 left-0 right-0 z-40 border-t border-purple-900/20 px-4 py-3" id="reader-controls">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between gap-4 mb-2">
            @if($prevSlug)
            <a href="{{ route('chapter.show', $prevSlug) }}" 
               class="flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-500 hover:border-accent/40 text-slate-300 hover:text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-all flex-1 justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Previous
            </a>
            @else
            <div class="flex-1"></div>
            @endif

            <a href="{{ route('manga.show', $mangaSlug) }}" 
               class="flex items-center gap-1.5 text-slate-500 hover:text-accent-light text-xs transition-colors px-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Chapters
            </a>

            @if($nextSlug)
            <a href="{{ route('chapter.show', $nextSlug) }}" 
               class="flex items-center gap-2 bg-accent hover:bg-accent-dark text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-all hover:shadow-lg hover:shadow-accent/30 flex-1 justify-center">
                Next
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @else
            <div class="flex-1 text-center text-xs text-slate-600 py-2">End of chapters</div>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-600 w-4">1</span>
            <div class="flex-1 h-1.5 bg-dark-600 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-accent to-neon-blue rounded-full transition-all duration-150" id="progress-fill" style="width: 0%"></div>
            </div>
            <span class="text-xs text-slate-600">{{ count($images) }}</span>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const totalPages = {{ count($images) }};
    let loadedImages = new Set();
    let autoHideTimer = null;
    let controlsVisible = true;

    const topBar     = document.getElementById('reader-top-bar');
    const bottomBar  = document.getElementById('reader-controls');

    // ── Show controls & reset auto-hide timer ──
    function showControls() {
        controlsVisible = true;
        topBar.classList.remove('hidden-bar');
        bottomBar.classList.remove('hidden-bar');
        resetHideTimer();
    }

    // ── Hide controls immediately ──
    function hideControls() {
        controlsVisible = false;
        topBar.classList.add('hidden-bar');
        bottomBar.classList.add('hidden-bar');
        clearTimeout(autoHideTimer);
    }

    // ── Auto-hide after 3 seconds of inactivity ──
    function resetHideTimer() {
        clearTimeout(autoHideTimer);
        autoHideTimer = setTimeout(() => {
            hideControls();
        }, 3000);
    }

    // Start auto-hide on page load
    resetHideTimer();

    // ── Tap anywhere → show controls (and restart timer) ──
    function handleReaderClick(event) {
        if (!controlsVisible) {
            // Controls hidden → tap shows them
            showControls();
        } else {
            // Controls visible → tap resets the timer
            resetHideTimer();
        }
    }

    // ── Keep controls visible while hovering over them (desktop) ──
    [topBar, bottomBar].forEach(el => {
        el.addEventListener('mouseenter', () => clearTimeout(autoHideTimer));
        el.addEventListener('mouseleave', () => resetHideTimer());
    });

    // ── Intersection Observer for lazy loading ──
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const index = parseInt(entry.target.dataset.index);
                loadImage(index);
                if (index + 1 < totalPages) loadImage(index + 1);
                if (index + 2 < totalPages) loadImage(index + 2);
                imageObserver.unobserve(entry.target);
            }
        });
    }, { rootMargin: '200px 0px' });

    document.querySelectorAll('.page-wrapper').forEach(w => imageObserver.observe(w));
    [0, 1, 2].forEach(i => loadImage(i));

    function loadImage(index) {
        if (loadedImages.has(index)) return;
        const img = document.getElementById('img-' + index);
        if (!img || !img.dataset.src) return;
        loadedImages.add(index);
        img.src = img.dataset.src;
    }

    function imageLoaded(index) {
        const img      = document.getElementById('img-' + index);
        const skeleton = document.getElementById('skeleton-' + index);
        if (img) img.classList.add('loaded');
        if (skeleton) skeleton.style.display = 'none';
    }

    function imageError(index) {
        const skeleton = document.getElementById('skeleton-' + index);
        if (skeleton) {
            skeleton.innerHTML = '<div class="flex items-center justify-center py-8 text-slate-600 text-sm">Image failed to load</div>';
            skeleton.style.animation = 'none';
            skeleton.style.background = '#1a1a2e';
        }
    }

    // ── Scroll progress ──
    function updateProgress() {
        const scrollTop  = window.pageYOffset || document.documentElement.scrollTop;
        const docHeight  = document.documentElement.scrollHeight - window.innerHeight;
        const progress   = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

        document.getElementById('progress-bar').style.width   = progress + '%';
        document.getElementById('progress-fill').style.width  = progress + '%';
        document.getElementById('current-page').textContent   = Math.max(1, Math.ceil((progress / 100) * totalPages));
    }

    window.addEventListener('scroll', updateProgress, { passive: true });

    // ── Fullscreen ──
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    }

    // ── Keyboard shortcuts ──
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowDown' || e.key === 'PageDown') {
            window.scrollBy({ top: window.innerHeight * 0.8, behavior: 'smooth' });
        } else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
            window.scrollBy({ top: -window.innerHeight * 0.8, behavior: 'smooth' });
        } else if (e.key === 'h' || e.key === 'H') {
            controlsVisible ? hideControls() : showControls();
        }
    });

    // ── Save reading progress ──
    window.addEventListener('beforeunload', () => {
        fetch('{{ route("history.add") }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                chapter_slug: '{{ $chapterSlug }}',
                chapter_title: '{{ addslashes($chapterTitle) }}',
                manga_slug: '{{ $mangaSlug }}'
            }),
            keepalive: true
        });
    });
</script>
@endpush