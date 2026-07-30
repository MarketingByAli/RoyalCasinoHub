@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">Wallet adjustment</h1>
<p class="text-gray-500 text-sm mb-6">Enter a user ID, confirm their username/email/ID, and submit once. Idempotency key prevents double-submit credits.</p>
@if($errors->any())
    <div class="mb-4 text-red-400 text-sm">{{ $errors->first() }}</div>
@endif
@if(session('success'))
    <div class="mb-4 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 text-red-400 text-sm">{{ session('error') }}</div>
@endif
<form method="POST" action="#" class="max-w-md space-y-4" id="wallet-adjust-form">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
    <div>
        <label class="block text-sm text-gray-400 mb-1">User ID</label>
        <input type="number" id="wallet-user-id" name="user_id" value="{{ old('user_id', request('user_id')) }}" min="1" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Confirm username, email, or ID</label>
        <input name="confirm_username" value="{{ old('confirm_username') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2" placeholder="Must match the target user">
    </div>
    <div><label class="block text-sm text-gray-400 mb-1">Amount (+/-)</label><input name="amount" type="number" step="1" value="{{ old('amount') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
    <div>
        <label class="block text-sm text-gray-400 mb-1">Reason code</label>
        <select name="reason_code" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
            <option value="goodwill">goodwill</option>
            <option value="correction">correction</option>
            <option value="promotion">promotion</option>
            <option value="dispute_adjustment">dispute_adjustment</option>
            <option value="other">other</option>
        </select>
    </div>
    <div><label class="block text-sm text-gray-400 mb-1">Market ID (optional)</label><input name="market_id" type="number" value="{{ old('market_id') }}" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
    <div><label class="block text-sm text-gray-400 mb-1">Reason</label><input name="reason" value="{{ old('reason') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
    <div><label class="block text-sm text-gray-400 mb-1">Confirm reason</label><input name="confirm_reason" value="{{ old('confirm_reason') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
    <button type="submit" class="bg-amber-500 text-amber-950 px-4 py-2 rounded font-semibold">Apply adjustment</button>
</form>
<p class="text-gray-500 text-sm mt-6">Account restrictions: open <code class="text-gray-400">/admin/betting/users/{id}/account</code> after looking up the user ID.</p>
<script>
(function () {
    const form = document.getElementById('wallet-adjust-form');
    const input = document.getElementById('wallet-user-id');
    function syncAction() {
        const id = String(input.value || '').trim();
        if (!id) {
            form.action = '#';
            return;
        }
        form.action = @json(url('/admin/betting/users')).replace(/\/$/, '') + '/' + id + '/wallet-adjust';
    }
    input.addEventListener('change', syncAction);
    input.addEventListener('input', syncAction);
    form.addEventListener('submit', function (e) {
        syncAction();
        if (!input.value) {
            e.preventDefault();
            alert('Enter a valid user ID.');
        }
    });
    syncAction();
})();
</script>
@endsection
