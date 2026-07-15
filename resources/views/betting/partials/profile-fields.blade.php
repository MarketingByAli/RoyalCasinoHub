@php $minimumAge = $minimumAge ?? config('betting.minimum_age', 18); @endphp
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-300">Username</label>
    <input type="text" name="username" value="{{ old('username') }}" required pattern="[A-Za-z0-9_-]+" minlength="3" maxlength="32"
        class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white @error('username') border-red-500 @enderror">
    @error('username')<p class="text-red-400 text-sm">{{ $message }}</p>@enderror
</div>
<div class="grid sm:grid-cols-2 gap-4">
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-300">Country</label>
        <input type="text" name="country" value="{{ old('country', 'ES') }}" required maxlength="2" placeholder="ES"
            class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white uppercase">
    </div>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-300">Language</label>
        <select name="language" class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white">
            <option value="en" @selected(old('language', 'en') === 'en')>English</option>
            <option value="es" @selected(old('language') === 'es')>Español</option>
        </select>
    </div>
</div>
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-300">Date of birth</label>
    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required max="{{ now()->subYears($minimumAge)->format('Y-m-d') }}"
        class="w-full bg-slate-950/60 border border-amber-900/35 rounded-xl px-4 py-2.5 text-white @error('date_of_birth') border-red-500 @enderror">
    @error('date_of_birth')<p class="text-red-400 text-sm">{{ $message }}</p>@enderror
</div>
<div class="space-y-3 text-sm text-gray-400 border-t border-amber-900/20 pt-4">
    <label class="flex gap-2"><input type="checkbox" name="accept_terms" value="1" required @checked(old('accept_terms')) class="rounded border-amber-800"> I accept the <a href="{{ route('terms') }}" class="text-amber-400 underline" target="_blank">Terms</a></label>
    <label class="flex gap-2"><input type="checkbox" name="accept_gambling_rules" value="1" required @checked(old('accept_gambling_rules')) class="rounded border-amber-800"> I accept the <a href="{{ route('betting-rules') }}" class="text-amber-400 underline" target="_blank">betting rules</a></label>
    <label class="flex gap-2"><input type="checkbox" name="accept_privacy" value="1" required @checked(old('accept_privacy')) class="rounded border-amber-800"> I accept the <a href="{{ route('privacy') }}" class="text-amber-400 underline" target="_blank">Privacy Policy</a></label>
    <label class="flex gap-2"><input type="checkbox" name="accept_responsible_gambling" value="1" required @checked(old('accept_responsible_gambling')) class="rounded border-amber-800"> I acknowledge responsible gambling information</label>
    <label class="flex gap-2"><input type="checkbox" name="accept_customer_funds" value="1" required @checked(old('accept_customer_funds')) class="rounded border-amber-800"> I understand play-money has no cash value (Stage 0)</label>
    <label class="flex gap-2"><input type="checkbox" name="accept_marketing" value="1" @checked(old('accept_marketing')) class="rounded border-amber-800"> Send me marketing emails (optional)</label>
</div>
