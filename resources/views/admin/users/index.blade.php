@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-6">Users</h1>

<form method="GET" class="flex flex-wrap gap-4 mb-6">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
        class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white max-w-xs">
    <select name="role" class="bg-slate-800/50 border border-amber-900/30 rounded-lg px-4 py-2 text-white">
        <option value="">All roles</option>
        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
        <option value="casino_owner" {{ request('role') === 'casino_owner' ? 'selected' : '' }}>Casino owner</option>
        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
    </select>
    <button type="submit" class="bg-amber-500/20 text-amber-400 px-4 py-2 rounded-lg hover:bg-amber-500/30">Filter</button>
</form>

<div class="overflow-x-auto border border-amber-900/30 rounded-xl">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-900/80 text-gray-400">
            <tr>
                <th class="p-3">ID</th>
                <th class="p-3">Name</th>
                <th class="p-3">Email</th>
                <th class="p-3">Verified</th>
                <th class="p-3">Role &amp; status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr class="border-t border-amber-900/20">
                    <td class="p-3 text-gray-500">{{ $user->id }}</td>
                    <td class="p-3 text-white">{{ $user->name }}</td>
                    <td class="p-3 text-gray-300">{{ $user->email }}</td>
                    <td class="p-3 text-gray-400 text-xs">{{ $user->email_verified_at ? $user->email_verified_at->format('Y-m-d') : '—' }}</td>
                    <td class="p-3">
                        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="flex flex-wrap items-center gap-2">
                            @csrf
                            @method('PUT')
                            <select name="role" class="bg-slate-800/50 border border-amber-900/30 rounded px-2 py-1 text-white text-xs">
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>user</option>
                                <option value="casino_owner" {{ $user->role === 'casino_owner' ? 'selected' : '' }}>casino_owner</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
                            </select>
                            <select name="is_active" class="bg-slate-800/50 border border-amber-900/30 rounded px-2 py-1 text-white text-xs">
                                <option value="1" {{ $user->is_active ? 'selected' : '' }}>active</option>
                                <option value="0" {{ !$user->is_active ? 'selected' : '' }}>disabled</option>
                            </select>
                            <button type="submit" class="text-amber-400 hover:text-amber-300 text-xs font-medium">Save</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $users->links() }}</div>
@endsection
