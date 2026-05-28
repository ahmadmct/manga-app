@extends('layouts.app')

@section('title', 'CRUD Akun - MangAlfa')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 animate-fade-in">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display font-700 text-2xl text-white">CRUD Akun</h1>
        <a href="{{ route('users.create') }}" class="bg-accent hover:bg-accent-dark text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all">Buat Akun</a>
    </div>

    @if(session('status'))
    <div class="mb-4 rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-300">
        {{ session('status') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="bg-dark-800/50 border border-dark-600 rounded-2xl overflow-hidden">
        <div class="divide-y divide-dark-600">
            @foreach($users as $user)
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($user->is_admin)
                    <span class="text-xs px-2 py-1 rounded-lg bg-accent/20 text-accent-light border border-accent/30">Admin</span>
                    @endif
                    <a href="{{ route('users.edit', $user) }}" class="text-xs px-3 py-2 rounded-lg bg-dark-700 hover:bg-dark-600 text-slate-300 transition-colors">Edit</a>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-xs px-3 py-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-300 border border-red-500/20 transition-colors">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection
