@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">301 Redirects</h1>

<form action="{{ route('admin.redirects.store') }}" method="POST" class="max-w-2xl mb-8 p-6 bg-slate-800/50 border border-amber-900/30 rounded-xl">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-400 mb-1">From URL</label>
            <input type="text" name="from_url" required placeholder="/old-page" class="w-full bg-slate-900 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">To URL</label>
            <input type="text" name="to_url" required placeholder="/new-page or https://..." class="w-full bg-slate-900 border border-amber-900/30 rounded-lg px-4 py-2">
        </div>
    </div>
    <div class="mt-4">
        <label class="block text-sm text-gray-400 mb-1">Status Code</label>
        <select name="status_code" class="bg-slate-900 border border-amber-900/30 rounded-lg px-4 py-2">
            <option value="301">301 Permanent</option>
            <option value="302">302 Temporary</option>
        </select>
    </div>
    <button type="submit" class="mt-4 bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg">Add Redirect</button>
</form>

<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="border-b border-amber-900/30">
                <th class="text-left py-3 px-4">From</th>
                <th class="text-left py-3 px-4">To</th>
                <th class="text-left py-3 px-4">Code</th>
                <th class="text-left py-3 px-4">Hits</th>
                <th class="text-left py-3 px-4"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($redirects as $redirect)
                <tr class="border-b border-amber-900/20">
                    <td class="py-3 px-4 text-gray-400">{{ $redirect->from_url }}</td>
                    <td class="py-3 px-4 text-amber-400">{{ $redirect->to_url }}</td>
                    <td class="py-3 px-4">{{ $redirect->status_code }}</td>
                    <td class="py-3 px-4">{{ $redirect->hits }}</td>
                    <td class="py-3 px-4">
                        <form action="{{ route('admin.redirects.destroy', $redirect) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $redirects->links() }}</div>
@endsection
