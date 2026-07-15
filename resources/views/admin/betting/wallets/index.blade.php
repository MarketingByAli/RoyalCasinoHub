@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">Wallet adjustment</h1>
<p class="text-gray-500 text-sm mb-6">Enter user ID from Admin → Users. Dual reason confirmation required. Negative amount deducts points.</p>
@if($errors->any())
    <div class="mb-4 text-red-400 text-sm">{{ $errors->first() }}</div>
@endif
<form method="POST" action="{{ route('admin.betting.wallets.adjust', ['user' => old('user_id', request('user_id', 1))]) }}" class="max-w-md space-y-4" id="wallet-adjust-form">
    @csrf
    <div>
        <label class="block text-sm text-gray-400 mb-1">User ID</label>
        <input type="number" id="wallet-user-id" value="{{ old('user_id', request('user_id')) }}" min="1" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
    </div>
    <div><label class="block text-sm text-gray-400 mb-1">Amount (+/-)</label><input name="amount" type="number" step="1" value="{{ old('amount') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
    <div><label class="block text-sm text-gray-400 mb-1">Reason</label><input name="reason" value="{{ old('reason') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
    <div><label class="block text-sm text-gray-400 mb-1">Confirm reason</label><input name="confirm_reason" value="{{ old('confirm_reason') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
    <button type="submit" class="bg-amber-500 text-amber-950 px-4 py-2 rounded font-semibold">Apply adjustment</button>
</form>
<script>
(function () {
    const form = document.getElementById('wallet-adjust-form');
    const input = document.getElementById('wallet-user-id');
    function syncAction() {
        const id = input.value || '1';
        form.action = @json(url('/admin/betting/users')).replace(/\/$/, '') + '/' + id + '/wallet-adjust';
    }
    input.addEventListener('change', syncAction);
    input.addEventListener('input', syncAction);
    syncAction();
})();
</script>
@endsection
