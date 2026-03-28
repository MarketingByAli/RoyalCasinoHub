<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Services\EnrichmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $regionIndex = array_search('region', $header);
        $localityIndex = array_search('locality', $header);
        $linkedinIndex = $this->linkedinColumnIndex($header);

        if ($nameIndex === false || $countryIndex === false) {
            fclose($handle);

            return back()->with('error', 'CSV must have "name" and "country" columns. Found: '.implode(', ', $header));
        }

        $imported = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $name = trim($row[$nameIndex] ?? '');
            $country = trim($row[$countryIndex] ?? '');
            $website = isset($row[$websiteIndex]) ? trim($row[$websiteIndex]) : null;
            $website = $website === '' ? null : $website;
            $region = $this->csvCell($row, $regionIndex);
            $locality = $this->csvCell($row, $localityIndex);
            $linkedin = $this->csvCell($row, $linkedinIndex);

            $validator = Validator::make(
                [
                    'name' => $name,
                    'country' => $country,
                    'website' => $website,
                    'region' => $region,
                    'locality' => $locality,
                    'linkedin' => $linkedin,
                ],
                [
                    'name' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                    'website' => 'nullable|url|max:500',
                    'region' => 'nullable|string|max:255',
                    'locality' => 'nullable|string|max:255',
                    'linkedin' => 'nullable|url|max:500',
                ],
                [
                    'name.required' => 'Name is required.',
                    'name.max' => 'Name must not exceed 255 characters.',
                    'country.required' => 'Country is required.',
                    'country.max' => 'Country must not exceed 255 characters.',
                    'website.url' => 'Website must be a valid URL.',
                    'linkedin.url' => 'LinkedIn must be a valid URL.',
                ]
            );

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin),
                    'messages' => $validator->errors()->all(),
                ];

                continue;
            }

            $dupMsg = $this->duplicateRowMessage($website, $name, $country);
            if ($dupMsg !== null) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin),
                    'messages' => [$dupMsg],
                ];

                continue;
            }

            try {
                $slug = Str::slug($name);
                $baseSlug = $slug ?: 'casino';
                $counter = 1;
                while (Casino::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                $casino = Casino::create([
                    'name' => $name,
                    'slug' => $slug,
                    'country' => $country,
                    'country_slug' => Str::slug($country),
                    'region' => $region,
                    'locality' => $locality,
                    'website' => $website,
                    'social_links' => Casino::mergeSocialLinks(null, $linkedin),
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
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin),
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
            'rows.*.region' => 'nullable|string|max:255',
            'rows.*.locality' => 'nullable|string|max:255',
            'rows.*.linkedin' => 'nullable|string|max:500',
            'row_offset' => 'required|integer|min:0',
        ]);

        $imported = 0;
        $errors = [];
        $rowOffset = $validated['row_offset'];

        foreach ($validated['rows'] as $index => $row) {
            $rowNumber = $rowOffset + $index + 1;
            $name = trim($row['name'] ?? '');
            $country = trim($row['country'] ?? '');
            $website = ! empty($row['website']) ? trim($row['website']) : null;
            $region = isset($row['region']) ? trim($row['region']) : '';
            $region = $region === '' ? null : $region;
            $locality = isset($row['locality']) ? trim($row['locality']) : '';
            $locality = $locality === '' ? null : $locality;
            $linkedin = ! empty($row['linkedin']) ? trim($row['linkedin']) : null;

            $validator = Validator::make(
                [
                    'name' => $name,
                    'country' => $country,
                    'website' => $website,
                    'region' => $region,
                    'locality' => $locality,
                    'linkedin' => $linkedin,
                ],
                [
                    'name' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                    'website' => 'nullable|url|max:500',
                    'region' => 'nullable|string|max:255',
                    'locality' => 'nullable|string|max:255',
                    'linkedin' => 'nullable|url|max:500',
                ],
                [
                    'website.url' => 'Website must be a valid URL.',
                    'linkedin.url' => 'LinkedIn must be a valid URL.',
                ]
            );

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin),
                    'messages' => $validator->errors()->all(),
                ];

                continue;
            }

            $dupMsg = $this->duplicateRowMessage($website, $name, $country);
            if ($dupMsg !== null) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin),
                    'messages' => [$dupMsg],
                ];

                continue;
            }

            try {
                $slug = Str::slug($name);
                $baseSlug = $slug ?: 'casino';
                $counter = 1;
                while (Casino::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter++;
                }

                $casino = Casino::create([
                    'name' => $name,
                    'slug' => $slug,
                    'country' => $country,
                    'country_slug' => Str::slug($country),
                    'region' => $region,
                    'locality' => $locality,
                    'website' => $website,
                    'social_links' => Casino::mergeSocialLinks(null, $linkedin),
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
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin),
                    'messages' => ['Failed to import this row. Please check the data and try again.'],
                ];
            }
        }

        return response()->json([
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    /**
     * @param  array<int, string>  $header
     */
    private function linkedinColumnIndex(array $header): int|false
    {
        foreach (['linkedin', 'social_linkedin'] as $col) {
            $i = array_search($col, $header, true);
            if ($i !== false) {
                return $i;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $row
     */
    private function csvCell(array $row, int|false $index): ?string
    {
        if ($index === false) {
            return null;
        }
        $v = trim($row[$index] ?? '');

        return $v === '' ? null : $v;
    }

    /**
     * @return array{name: string, country: string, website: ?string, region: ?string, locality: ?string, linkedin: ?string}
     */
    private function importErrorData(
        string $name,
        string $country,
        ?string $website,
        ?string $region,
        ?string $locality,
        ?string $linkedin
    ): array {
        return [
            'name' => $name,
            'country' => $country,
            'website' => $website,
            'region' => $region,
            'locality' => $locality,
            'linkedin' => $linkedin,
        ];
    }

    private function duplicateRowMessage(?string $website, string $name, string $country): ?string
    {
        $countrySlug = Str::slug($country);

        if ($website) {
            $host = $this->normalizedHost($website);
            if ($host) {
                $exists = Casino::query()->whereNotNull('website')->get()
                    ->contains(fn ($c) => $this->normalizedHost((string) $c->website) === $host);
                if ($exists) {
                    return 'A casino with this website domain may already exist.';
                }
            }
        }

        if (Casino::query()->where('country_slug', $countrySlug)->where(function ($q) use ($name) {
            $q->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
        })->exists()) {
            return 'Possible duplicate casino name for this country.';
        }

        return null;
    }

    private function normalizedHost(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $url = trim($url);
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            $url = 'https://'.$url;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return null;
        }
        $host = strtolower($host);
        if (Str::startsWith($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }
}
