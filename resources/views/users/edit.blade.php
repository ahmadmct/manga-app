@extends('layouts.app')

@section('title', 'Edit Akun - MangAlfa')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 py-8 animate-fade-in">
    <h1 class="font-display font-700 text-2xl text-white mb-6">Edit Akun</h1>

    <form action="{{ route('users.update', $user) }}" method="POST" class="bg-dark-800/50 border border-dark-600 rounded-2xl p-6 space-y-4">
        @csrf
        @method('PUT')
        @include('users.partials.form', ['user' => $user])
        <button class="w-full bg-accent hover:bg-accent-dark text-white py-2.5 rounded-xl text-sm font-semibold transition-all">Update</button>
    </form>
</div>
@endsection
