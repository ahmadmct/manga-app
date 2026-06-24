@extends('layouts.app')

@section('title', 'Verifikasi 1 - MangAlfa')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-16 animate-fade-in">
    <div class="bg-dark-800/60 border border-dark-600 rounded-2xl p-6">
        <h1 class="font-display font-700 text-2xl text-white mb-2">
            Verifikasi Keamanan
        </h1>

        <p class="text-slate-400 text-sm mb-6">
            Masukkan jawaban yang benar untuk melanjutkan.
        </p>

        @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('unlock.step1.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm text-slate-400 mb-1">
                    Ketik:
                    <span class="text-accent font-semibold">APALAH SESUKAMU</span>
                </label>

                <input
                    type="text"
                    name="answer"
                    required
                    autofocus
                    autocomplete="off"
                    class="w-full bg-dark-700 border border-dark-500 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-accent">
            </div>

            <button
                type="submit"
                class="w-full bg-accent hover:bg-accent-dark text-white py-2.5 rounded-xl text-sm font-semibold transition-all">
                Lanjutkan
            </button>
        </form>
    </div>
</div>
@endsection