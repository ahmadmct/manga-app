<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MangAlfa') — Read Manga Online</title>
    <meta name="description" content="@yield('description', 'MangAlfa — Your premium manga reading destination. Read the latest manga chapters online for free.')">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Space Grotesk', 'sans-serif'],
                    display: ['Syne', 'sans-serif'],
                },
                colors: {
                    dark: {
                        950: '#050508',
                        900: '#0a0a12',
                        800: '#111120',
                        700: '#1a1a2e',
                        600: '#22223b',
                        500: '#2d2d44',
                    },
                    accent: {
                        DEFAULT: '#7c3aed',
                        light: '#a78bfa',
                        dark: '#5b21b6',
                        glow: 'rgba(124, 58, 237, 0.4)',
                    },
                    neon: {
                        purple: '#b44aff',
                        blue: '#4a9eff',
                        pink: '#ff4ab0',
                    }
                },
                backgroundImage: {
                    'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                    'noise': "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E\")",
                },
                animation: {
                    'fade-in': 'fadeIn 0.4s ease-out',
                    'slide-up': 'slideUp 0.4s ease-out',
                    'pulse-slow': 'pulse 3s ease-in-out infinite',
                    'shimmer': 'shimmer 1.5s linear infinite',
                },
                keyframes: {
                    fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                    slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                    shimmer: { '0%': { backgroundPosition: '-1000px 0' }, '100%': { backgroundPosition: '1000px 0' } },
                }
            }
        }
    }
    </script>

    <style>
        * { box-sizing: border-box; }
        body { background-color: #050508; color: #e2e8f0; font-family: 'Space Grotesk', sans-serif; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0a0a12; }
        ::-webkit-scrollbar-thumb { background: #7c3aed; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a78bfa; }

        .skeleton {
            background: linear-gradient(90deg, #1a1a2e 25%, #22223b 50%, #1a1a2e 75%);
            background-size: 1000px 100%;
            animation: shimmer 1.5s linear infinite;
        }

        .manga-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .manga-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(124, 58, 237, 0.2); }

        .glow-purple { box-shadow: 0 0 20px rgba(124, 58, 237, 0.5), 0 0 40px rgba(124, 58, 237, 0.2); }
        .glow-text { text-shadow: 0 0 20px rgba(167, 139, 250, 0.8); }

        .glass {
            background: rgba(10, 10, 18, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(124, 58, 237, 0.15);
        }

        .nav-item { position: relative; }
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: #7c3aed;
            border-radius: 50%;
        }

        .bottom-nav-item.active svg { color: #a78bfa; }
        .bottom-nav-item.active span { color: #a78bfa; }

        .chapter-img { user-select: none; -webkit-user-drag: none; }
        
        .prose-dark a { color: #a78bfa; }

        @media (max-width: 768px) {
            .main-content { padding-bottom: 5rem; }
        }

        .badge-new { 
            background: linear-gradient(135deg, #7c3aed, #4a9eff);
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .line-clamp-2 { 
            display: -webkit-box; 
            -webkit-line-clamp: 2; 
            -webkit-box-orient: vertical; 
            overflow: hidden; 
        }
        .line-clamp-3 { 
            display: -webkit-box; 
            -webkit-line-clamp: 3; 
            -webkit-box-orient: vertical; 
            overflow: hidden; 
        }

        /* Neon border effect */
        .border-neon {
            border: 1px solid transparent;
            background-clip: padding-box;
            position: relative;
        }
        .border-neon::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: linear-gradient(135deg, #7c3aed33, #4a9eff33);
            z-index: -1;
        }
    </style>
    @stack('head')
</head>
<body class="bg-dark-950 text-slate-200 antialiased">

<!-- Top Navigation -->
<nav class="glass sticky top-0 z-50 border-b border-purple-900/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-14">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-lg bg-accent flex items-center justify-center glow-purple">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="font-display font-800 text-xl text-white">Mang<span class="text-accent-light">Alfa</span></span>
            </a>

            <!-- Desktop Search -->
            <form action="{{ route('search') }}" method="GET" class="hidden md:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Search manga, manhwa..."
                        value="{{ request('q') }}"
                        class="w-full bg-dark-700 border border-purple-900/30 rounded-xl px-4 py-2 pl-10 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all"
                        autocomplete="off"
                    >
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-dark-700 transition-colors {{ request()->routeIs('home') ? 'text-accent-light bg-dark-700' : '' }}">Home</a>
                <a href="{{ route('manga.index') }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-dark-700 transition-colors {{ request()->routeIs('manga.*') ? 'text-accent-light bg-dark-700' : '' }}">Browse</a>
                <a href="{{ route('recommended.index') }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-dark-700 transition-colors {{ request()->routeIs('recommended.*') ? 'text-accent-light bg-dark-700' : '' }}">Recommended</a>
                <a href="{{ route('search') }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-dark-700 transition-colors {{ request()->routeIs('search') ? 'text-accent-light bg-dark-700' : '' }}">Search</a>
                <a href="{{ route('bookmarks.index') }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-dark-700 transition-colors {{ request()->routeIs('bookmarks.*') ? 'text-accent-light bg-dark-700' : '' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </a>
                @auth
                    @if(auth()->user()->is_admin)
                    <a href="{{ route('users.index') }}" class="px-3 py-1.5 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-dark-700 transition-colors {{ request()->routeIs('users.*') ? 'text-accent-light bg-dark-700' : '' }}">Users</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="px-3 py-1.5 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-dark-700 transition-colors">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg text-sm text-white bg-accent hover:bg-accent-dark transition-colors">Login</a>
                @endauth
            </div>

            <!-- Mobile Search Button -->
            <div class="flex md:hidden items-center gap-3">
                <a href="{{ route('search') }}" class="text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="main-content min-h-screen">
    @yield('content')
</main>

<!-- Mobile Bottom Navigation -->
@if(!request()->routeIs('chapter.show'))
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 glass border-t border-purple-900/20 px-2 py-2">
    <div class="flex items-center justify-around">
        <a href="{{ route('home') }}" class="bottom-nav-item flex flex-col items-center gap-0.5 py-1 px-3 rounded-xl {{ request()->routeIs('home') ? 'active' : '' }}">
            <svg class="w-5 h-5 text-slate-400" fill="{{ request()->routeIs('home') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs text-slate-400 {{ request()->routeIs('home') ? 'text-accent-light' : '' }}">Home</span>
        </a>
        <a href="{{ route('manga.index') }}" class="bottom-nav-item flex flex-col items-center gap-0.5 py-1 px-3 rounded-xl {{ request()->routeIs('manga.index') ? 'active' : '' }}">
            <svg class="w-5 h-5 text-slate-400" fill="{{ request()->routeIs('manga.index') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span class="text-xs text-slate-400 {{ request()->routeIs('manga.index') ? 'text-accent-light' : '' }}">Browse</span>
        </a>
        <a href="{{ route('search') }}" class="bottom-nav-item flex flex-col items-center gap-0.5 py-1 px-3 rounded-xl {{ request()->routeIs('search') ? 'active' : '' }}">
            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-xs text-slate-400 {{ request()->routeIs('search') ? 'text-accent-light' : '' }}">Search</span>
        </a>
        <a href="{{ route('bookmarks.index') }}" class="bottom-nav-item flex flex-col items-center gap-0.5 py-1 px-3 rounded-xl {{ request()->routeIs('bookmarks.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 text-slate-400" fill="{{ request()->routeIs('bookmarks.*') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            <span class="text-xs text-slate-400 {{ request()->routeIs('bookmarks.*') ? 'text-accent-light' : '' }}">Saved</span>
        </a>
        <a href="{{ route('history.index') }}" class="bottom-nav-item flex flex-col items-center gap-0.5 py-1 px-3 rounded-xl {{ request()->routeIs('history.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs text-slate-400 {{ request()->routeIs('history.*') ? 'text-accent-light' : '' }}">History</span>
        </a>
    </div>
</nav>
@endif

@stack('scripts')
</body>
</html>
