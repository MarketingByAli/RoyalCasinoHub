@extends('layouts.admin')

@section('content')
<h1 class="text-2xl font-bold text-amber-400 mb-8">CSV Import</h1>

<div class="max-w-3xl">
    <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Import Instructions</h2>
        <ul class="text-gray-400 space-y-2 text-sm mb-4">
            <li>• <strong class="text-amber-400">Required columns:</strong> <code class="bg-slate-900 px-1 rounded">name</code>, <code class="bg-slate-900 px-1 rounded">country</code></li>
            <li>• <strong class="text-amber-400">Optional columns:</strong> <code class="bg-slate-900 px-1 rounded">website</code>, <code class="bg-slate-900 px-1 rounded">region</code>, <code class="bg-slate-900 px-1 rounded">locality</code>, <code class="bg-slate-900 px-1 rounded">linkedin</code> (or <code class="bg-slate-900 px-1 rounded">social_linkedin</code>)</li>
            <li>• URLs must be valid when provided. Country listings sort by region, then locality, then name.</li>
            <li>• First row must be the header. Column names are case-insensitive.</li>
            <li>• Max file size: 10 MB. Accepted formats: .csv, .txt</li>
        </ul>
        <p class="text-gray-500 text-sm">Example CSV:</p>
        <pre class="bg-slate-900 rounded-lg p-4 mt-2 text-xs text-gray-300 overflow-x-auto">name,country,region,locality,website,linkedin
Example Casino,United Kingdom,England,London,https://example.com,https://www.linkedin.com/company/example
Another Casino,United States,Nevada,Las Vegas,https://another.example.com,</pre>
    </div>

    <form id="import-form" action="{{ route('admin.import.store') }}" method="POST" enctype="multipart/form-data" class="mb-8">
        @csrf
        <div class="flex flex-col sm:flex-row gap-4 items-start">
            <div class="flex-1 w-full">
                <input type="file" name="csv" id="csv-file" accept=".csv,.txt" required
                    class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500/20 file:text-amber-400 hover:file:bg-amber-500/30">
                @error('csv')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" id="submit-btn" class="bg-amber-500 hover:bg-amber-600 text-amber-950 font-semibold px-6 py-2 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Import
                </button>
                <button type="button" id="import-with-progress-btn" class="bg-slate-700 hover:bg-slate-600 text-gray-200 font-semibold px-6 py-2 rounded-lg transition border border-amber-900/30">
                    Import with Progress
                </button>
            </div>
        </div>
    </form>

    <div id="progress-container" class="hidden mb-8">
        <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
            <p id="progress-status" class="text-gray-400 mb-2">Preparing...</p>
            <div class="w-full bg-slate-900 rounded-full h-3 overflow-hidden">
                <div id="progress-bar" class="h-full bg-amber-500 transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="progress-detail" class="text-sm text-gray-500 mt-2"></p>
        </div>
    </div>

    <div id="batch-results-container" class="hidden mb-6"></div>

    @if(session('import_success'))
        <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Import Results</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <div class="bg-slate-900/50 rounded-lg p-4">
                    <p class="text-2xl font-bold text-emerald-400">{{ session('imported', 0) }}</p>
                    <p class="text-sm text-gray-400">Imported</p>
                </div>
                <div class="bg-slate-900/50 rounded-lg p-4">
                    <p class="text-2xl font-bold text-red-400">{{ count(session('errors', [])) }}</p>
                    <p class="text-sm text-gray-400">Errors</p>
                </div>
                <div class="bg-slate-900/50 rounded-lg p-4">
                    <p class="text-2xl font-bold text-white">{{ session('total_rows', 0) }}</p>
                    <p class="text-sm text-gray-400">Total Rows</p>
                </div>
                <div class="bg-slate-900/50 rounded-lg p-4">
                    <p class="text-2xl font-bold text-amber-400">{{ session('imported', 0) + count(session('errors', [])) }}</p>
                    <p class="text-sm text-gray-400">Processed</p>
                </div>
            </div>

            @if(count(session('errors', [])) > 0)
                <div class="mt-4">
                    <button type="button" id="toggle-errors" class="text-amber-400 hover:text-amber-300 font-medium text-sm mb-2">
                        Show {{ count(session('errors')) }} error(s) ▼
                    </button>
                    <div id="errors-list" class="hidden space-y-3 max-h-96 overflow-y-auto">
                        @foreach(session('errors') as $error)
                            <div class="bg-slate-900/50 border border-red-500/30 rounded-lg p-4">
                                <p class="font-semibold text-red-400 mb-1">Row {{ $error['row'] }}</p>
                                <p class="text-gray-400 text-sm mb-2">{{ implode(' ', $error['messages']) }}</p>
                                <p class="text-gray-500 text-xs font-mono">
                                    name: "{{ $error['data']['name'] ?? '' }}",
                                    country: "{{ $error['data']['country'] ?? '' }}",
                                    region: "{{ $error['data']['region'] ?? '' }}",
                                    locality: "{{ $error['data']['locality'] ?? '' }}",
                                    website: "{{ $error['data']['website'] ?? '' }}",
                                    linkedin: "{{ $error['data']['linkedin'] ?? '' }}"
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(session('success') && !session('import_success'))
        <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('import-form');
    const fileInput = document.getElementById('csv-file');
    const submitBtn = document.getElementById('submit-btn');
    const progressBtn = document.getElementById('import-with-progress-btn');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const progressStatus = document.getElementById('progress-status');
    const progressDetail = document.getElementById('progress-detail');

    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
    });

    const toggleErrors = document.getElementById('toggle-errors');
    const errorsList = document.getElementById('errors-list');
    if (toggleErrors && errorsList) {
        toggleErrors.addEventListener('click', function() {
            const isHidden = errorsList.classList.contains('hidden');
            errorsList.classList.toggle('hidden', !isHidden);
            toggleErrors.textContent = isHidden
                ? 'Hide errors ▲'
                : 'Show {{ count(session('errors', [])) }} error(s) ▼';
        });
    }

    progressBtn.addEventListener('click', async function() {
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a CSV file first.');
            return;
        }

        progressBtn.disabled = true;
        submitBtn.disabled = true;
        progressContainer.classList.remove('hidden');
        progressBar.style.width = '0%';
        progressStatus.textContent = 'Reading file...';

        const text = await file.text();
        const lines = text.split(/\r?\n/).filter(l => l.trim());
        if (lines.length < 2) {
            progressStatus.textContent = 'File is empty or has no data rows.';
            progressBtn.disabled = false;
            submitBtn.disabled = false;
            return;
        }

        const header = lines[0].split(',').map(h => h.trim().toLowerCase());
        const nameIdx = header.indexOf('name');
        const countryIdx = header.indexOf('country');
        const websiteIdx = header.indexOf('website');
        const regionIdx = header.indexOf('region');
        const localityIdx = header.indexOf('locality');
        let linkedinIdx = header.indexOf('linkedin');
        if (linkedinIdx === -1) linkedinIdx = header.indexOf('social_linkedin');

        if (nameIdx === -1 || countryIdx === -1) {
            progressStatus.textContent = 'CSV must have "name" and "country" columns.';
            progressBtn.disabled = false;
            submitBtn.disabled = false;
            return;
        }

        const rows = [];
        for (let i = 1; i < lines.length; i++) {
            const cols = parseCSVLine(lines[i]);
            const name = (cols[nameIdx] || '').trim();
            const country = (cols[countryIdx] || '').trim();
            const website = websiteIdx >= 0 ? (cols[websiteIdx] || '').trim() : '';
            const region = regionIdx >= 0 ? (cols[regionIdx] || '').trim() : '';
            const locality = localityIdx >= 0 ? (cols[localityIdx] || '').trim() : '';
            const linkedin = linkedinIdx >= 0 ? (cols[linkedinIdx] || '').trim() : '';
            rows.push({ name, country, website, region, locality, linkedin });
        }

        const BATCH_SIZE = 100;
        const totalBatches = Math.ceil(rows.length / BATCH_SIZE);
        let totalImported = 0;
        const allErrors = [];

        progressStatus.textContent = `Importing ${rows.length} rows in ${totalBatches} batches...`;

        for (let b = 0; b < totalBatches; b++) {
            const start = b * BATCH_SIZE;
            const batch = rows.slice(start, start + BATCH_SIZE);
            const rowOffset = start;

            progressDetail.textContent = `Batch ${b + 1} of ${totalBatches} (rows ${start + 1}-${Math.min(start + BATCH_SIZE, rows.length)})`;

            try {
                const res = await fetch('{{ route("admin.import.store-batch") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ rows: batch, row_offset: rowOffset }),
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Batch import failed');
                }
                totalImported += data.imported || 0;
                if (data.errors) allErrors.push(...data.errors);
                progressBar.style.width = (((b + 1) / totalBatches) * 100) + '%';
            } catch (err) {
                progressStatus.textContent = 'Import failed: ' + err.message;
                progressBar.style.width = '100%';
                progressBtn.disabled = false;
                submitBtn.disabled = false;
                return;
            }
        }

        progressBar.style.width = '100%';
        progressStatus.textContent = 'Complete!';
        progressDetail.textContent = `Imported ${totalImported} casinos. ${allErrors.length} error(s).`;

        progressBtn.disabled = false;
        submitBtn.disabled = false;

        showBatchResults(totalImported, allErrors, rows.length);
    });

    function esc(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str ?? ''));
        return div.innerHTML;
    }

    function showBatchResults(imported, errors, totalRows) {
        const container = document.getElementById('batch-results-container');
        container.classList.remove('hidden');
        container.innerHTML = `
            <div class="bg-slate-800/50 border border-amber-900/30 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Import Results</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div class="bg-slate-900/50 rounded-lg p-4">
                        <p class="text-2xl font-bold text-emerald-400">${parseInt(imported)}</p>
                        <p class="text-sm text-gray-400">Imported</p>
                    </div>
                    <div class="bg-slate-900/50 rounded-lg p-4">
                        <p class="text-2xl font-bold text-red-400">${parseInt(errors.length)}</p>
                        <p class="text-sm text-gray-400">Errors</p>
                    </div>
                    <div class="bg-slate-900/50 rounded-lg p-4">
                        <p class="text-2xl font-bold text-white">${parseInt(totalRows)}</p>
                        <p class="text-sm text-gray-400">Total Rows</p>
                    </div>
                    <div class="bg-slate-900/50 rounded-lg p-4">
                        <p class="text-2xl font-bold text-amber-400">${parseInt(imported) + parseInt(errors.length)}</p>
                        <p class="text-sm text-gray-400">Processed</p>
                    </div>
                </div>
                ${errors.length > 0 ? `
                    <div class="mt-4">
                        <button type="button" id="toggle-batch-errors" class="text-amber-400 hover:text-amber-300 font-medium text-sm mb-2">
                            Show ${parseInt(errors.length)} error(s) ▼
                        </button>
                        <div id="batch-errors-list" class="hidden space-y-3 max-h-96 overflow-y-auto">
                            ${errors.map(e => `
                                <div class="bg-slate-900/50 border border-red-500/30 rounded-lg p-4">
                                    <p class="font-semibold text-red-400 mb-1">Row ${parseInt(e.row)}</p>
                                    <p class="text-gray-400 text-sm mb-2">${esc((e.messages || []).join(' '))}</p>
                                    <p class="text-gray-500 text-xs font-mono">
                                        name: "${esc(e.data && e.data.name)}",
                                        country: "${esc(e.data && e.data.country)}",
                                        region: "${esc(e.data && e.data.region)}",
                                        locality: "${esc(e.data && e.data.locality)}",
                                        website: "${esc(e.data && e.data.website)}",
                                        linkedin: "${esc(e.data && e.data.linkedin)}"
                                    </p>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
        container.scrollIntoView({ behavior: 'smooth' });

        const toggleBtn = document.getElementById('toggle-batch-errors');
        const errorsList = document.getElementById('batch-errors-list');
        if (toggleBtn && errorsList) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = errorsList.classList.contains('hidden');
                errorsList.classList.toggle('hidden', !isHidden);
                toggleBtn.textContent = isHidden ? 'Hide errors ▲' : `Show ${errors.length} error(s) ▼`;
            });
        }
    }

    function parseCSVLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
            const c = line[i];
            if (c === '"') {
                inQuotes = !inQuotes;
            } else if ((c === ',' && !inQuotes) || c === '\n') {
                result.push(current);
                current = '';
            } else {
                current += c;
            }
        }
        result.push(current);
        return result;
    }
});
</script>
@endsection
