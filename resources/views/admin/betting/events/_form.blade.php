@php $event = $event ?? null; @endphp
<div><label class="block text-sm text-gray-400 mb-1">Title</label><input name="title" value="{{ old('title', $event?->title) }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
<div><label class="block text-sm text-gray-400 mb-1">Category</label><input name="category" value="{{ old('category', $event?->category ?? 'sport') }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
<div><label class="block text-sm text-gray-400 mb-1">Organiser</label><input name="organiser" value="{{ old('organiser', $event?->organiser) }}" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
<div><label class="block text-sm text-gray-400 mb-1">Location</label><input name="location" value="{{ old('location', $event?->location) }}" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
<div><label class="block text-sm text-gray-400 mb-1">Start at</label><input type="datetime-local" name="start_at" value="{{ old('start_at', $event?->start_at?->format('Y-m-d\TH:i')) }}" required class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
<div><label class="block text-sm text-gray-400 mb-1">Completes at</label><input type="datetime-local" name="completes_at" value="{{ old('completes_at', $event?->completes_at?->format('Y-m-d\TH:i')) }}" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
<div><label class="block text-sm text-gray-400 mb-1">Betting close at</label><input type="datetime-local" name="betting_close_at" value="{{ old('betting_close_at', $event?->betting_close_at?->format('Y-m-d\TH:i')) }}" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
<div><label class="block text-sm text-gray-400 mb-1">Settlement source</label><input name="settlement_source" value="{{ old('settlement_source', $event?->settlement_source) }}" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2"></div>
@if($event)
<div><label class="block text-sm text-gray-400 mb-1">Status</label>
    <select name="status" class="w-full bg-slate-800 border border-amber-900/30 rounded px-3 py-2">
        @foreach(['scheduled','in_progress','completed','cancelled'] as $s)
            <option value="{{ $s }}" @selected(old('status', $event->status) === $s)>{{ $s }}</option>
        @endforeach
    </select>
</div>
@endif
