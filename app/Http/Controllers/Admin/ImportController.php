<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Services\EnrichmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.import.index');
    }

    public function store(Request $request, EnrichmentService $enrichmentService)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return back()->with('error', 'Could not read the CSV file.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        $header = array_map('strtolower', array_map('trim', $header));
        $nameIndex = array_search('name', $header);
        $countryIndex = array_search('country', $header);
        $websiteIndex = array_search('website', $header);

        if ($nameIndex === false || $countryIndex === false) {
            fclose($handle);
            return back()->with('error', 'CSV must have "name" and "country" columns. Found: ' . implode(', ', $header));
        }

        $imported = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $name = trim($row[$nameIndex] ?? '');
            $country = trim($row[$countryIndex] ?? '');
            $website = isset($row[$websiteIndex]) ? trim($row[$websiteIndex]) : null;

            $validator = Validator::make(
                [
                    'name' => $name,
                    'country' => $country,
                    'website' => $website,
                ],
                [
                    'name' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                    'website' => 'nullable|url|max:500',
                ],
                [
                    'name.required' => 'Name is required.',
                    'name.max' => 'Name must not exceed 255 characters.',
                    'country.required' => 'Country is required.',
                    'country.max' => 'Country must not exceed 255 characters.',
                    'website.url' => 'Website must be a valid URL.',
                ]
            );

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => compact('name', 'country', 'website'),
                    'messages' => $validator->errors()->all(),
                ];
                continue;
            }

            try {
                $slug = Str::slug($name);
                $baseSlug = $slug ?: 'casino';
                $counter = 1;
                while (Casino::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $casino = Casino::create([
                    'name' => $name,
                    'slug' => $slug,
                    'country' => $country,
                    'country_slug' => Str::slug($country),
                    'website' => $website ?: null,
                ]);
                $casino->status = 'draft';
                $casino->enrichment_status = 'pending';
                $casino->save();

                $enrichmentService->createEnrichmentJobs($casino);
                $imported++;
            } catch (\Throwable $e) {
                report($e);
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => compact('name', 'country', 'website'),
                    'messages' => ['Failed to import this row. Please check the data and try again.'],
                ];
            }
        }

        fclose($handle);

        return back()->with([
            'import_success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'total_rows' => $rowNumber - 1,
        ]);
    }

    public function storeBatch(Request $request, EnrichmentService $enrichmentService): JsonResponse
    {
        $validated = $request->validate([
            'rows' => 'required|array|max:500',
            'rows.*.name' => 'nullable|string|max:255',
            'rows.*.country' => 'nullable|string|max:255',
            'rows.*.website' => 'nullable|string|max:500',
            'row_offset' => 'required|integer|min:0',
        ]);

        $imported = 0;
        $errors = [];
        $rowOffset = $validated['row_offset'];

        foreach ($validated['rows'] as $index => $row) {
            $rowNumber = $rowOffset + $index + 1;
            $name = trim($row['name'] ?? '');
            $country = trim($row['country'] ?? '');
            $website = !empty($row['website']) ? trim($row['website']) : null;

            if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => compact('name', 'country', 'website'),
                    'messages' => ['Website must be a valid URL.'],
                ];
                continue;
            }

            if (empty($name) || empty($country)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => compact('name', 'country', 'website'),
                    'messages' => ['Name and country are required.'],
                ];
                continue;
            }

            try {
                $slug = Str::slug($name);
                $baseSlug = $slug ?: 'casino';
                $counter = 1;
                while (Casino::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $casino = Casino::create([
                    'name' => $name,
                    'slug' => $slug,
                    'country' => $country,
                    'country_slug' => Str::slug($country),
                    'website' => $website,
                ]);
                $casino->status = 'draft';
                $casino->enrichment_status = 'pending';
                $casino->save();

                $enrichmentService->createEnrichmentJobs($casino);
                $imported++;
            } catch (\Throwable $e) {
                report($e);
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => compact('name', 'country', 'website'),
                    'messages' => ['Failed to import this row. Please check the data and try again.'],
                ];
            }
        }

        return response()->json([
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }
}
