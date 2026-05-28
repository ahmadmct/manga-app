@if($errors->any())
<div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
    {{ $errors->first() }}
</div>
@endif

<div>
    <label class="block text-sm text-slate-400 mb-1" for="name">Nama</label>
    <input id="name" name="name" value="{{ old('name', $user?->name) }}" required
           class="w-full bg-dark-700 border border-dark-500 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-accent">
</div>

<div>
    <label class="block text-sm text-slate-400 mb-1" for="email">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required
           class="w-full bg-dark-700 border border-dark-500 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-accent">
</div>

<div>
    <label class="block text-sm text-slate-400 mb-1" for="password">Password {{ $user ? '(kosongkan jika tidak diganti)' : '' }}</label>
    <input id="password" name="password" type="password" {{ $user ? '' : 'required' }}
           class="w-full bg-dark-700 border border-dark-500 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-accent">
</div>

<div>
    <label class="block text-sm text-slate-400 mb-1" for="password_confirmation">Konfirmasi Password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" {{ $user ? '' : 'required' }}
           class="w-full bg-dark-700 border border-dark-500 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none focus:border-accent">
</div>

<label class="flex items-center gap-2 text-sm text-slate-300">
    <input type="checkbox" name="is_admin" value="1" class="rounded border-dark-500 bg-dark-700 text-accent"
           @checked(old('is_admin', $user?->is_admin))>
    Admin
</label>
