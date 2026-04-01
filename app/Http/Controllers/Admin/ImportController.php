<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino;
use App\Services\CasinoIntakeService;
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

    public function store(Request $request, EnrichmentService $enrichmentService, CasinoIntakeService $intake)
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
        $foundedIndex = $this->foundedColumnIndex($header);

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
            $website = $website === '' ? null : $intake->normalizeImportUrl($website);
            $region = $this->csvCell($row, $regionIndex);
            $locality = $this->csvCell($row, $localityIndex);
            $linkedin = $this->csvCell($row, $linkedinIndex);
            $linkedin = $linkedin !== null ? $intake->normalizeImportUrl($linkedin) : null;

            $foundedParsed = $intake->parseFoundedYearString($this->csvCell($row, $foundedIndex));
            if ($foundedParsed['error'] !== null) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
                    'messages' => [$foundedParsed['error']],
                ];

                continue;
            }
            $establishedYear = $foundedParsed['year'];

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
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
                    'messages' => $validator->errors()->all(),
                ];

                continue;
            }

            $dupMsg = $intake->duplicateRowMessage($website, $name, $country);
            if ($dupMsg !== null) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
                    'messages' => [$dupMsg],
                ];

                continue;
            }

            try {
                $slug = $intake->uniqueSlugForName($name);

                $casino = Casino::create([
                    'name' => $name,
                    'slug' => $slug,
                    'country' => $country,
                    'country_slug' => Str::slug($country),
                    'region' => $region,
                    'locality' => $locality,
                    'website' => $website,
                    'social_links' => Casino::mergeSocialLinks(null, $linkedin),
                    'established_year' => $establishedYear,
                ]);
                $casino->status = 'published';
                $casino->enrichment_status = 'pending';
                $casino->save();

                $enrichmentService->createEnrichmentJobs($casino);
                $imported++;
            } catch (\Throwable $e) {
                report($e);
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
                    'messages' => ['Failed to import this row. Please check the data and try again.'],
                ];
            }
        }

        fclose($handle);

        return back()->with([
            'import_success' => true,
            'imported' => $imported,
            'import_errors' => $errors,
            'total_rows' => $rowNumber - 1,
        ]);
    }

    public function storeBatch(Request $request, EnrichmentService $enrichmentService, CasinoIntakeService $intake): JsonResponse
    {
        $validated = $request->validate([
            'rows' => 'required|array|max:500',
            'rows.*.name' => 'nullable|string|max:255',
            'rows.*.country' => 'nullable|string|max:255',
            'rows.*.website' => 'nullable|string|max:500',
            'rows.*.region' => 'nullable|string|max:255',
            'rows.*.locality' => 'nullable|string|max:255',
            'rows.*.linkedin' => 'nullable|string|max:500',
            'rows.*.founded' => 'nullable|string|max:32',
            'rows.*.established_year' => 'nullable|string|max:32',
            'row_offset' => 'required|integer|min:0',
        ]);

        $imported = 0;
        $errors = [];
        $rowOffset = $validated['row_offset'];

        foreach ($validated['rows'] as $index => $row) {
            $rowNumber = $rowOffset + $index + 1;
            $name = trim($row['name'] ?? '');
            $country = trim($row['country'] ?? '');
            $website = ! empty($row['website']) ? $intake->normalizeImportUrl(trim($row['website'])) : null;
            $region = isset($row['region']) ? trim($row['region']) : '';
            $region = $region === '' ? null : $region;
            $locality = isset($row['locality']) ? trim($row['locality']) : '';
            $locality = $locality === '' ? null : $locality;
            $linkedin = ! empty($row['linkedin']) ? $intake->normalizeImportUrl(trim($row['linkedin'])) : null;

            $foundedRaw = $row['founded'] ?? $row['established_year'] ?? null;
            $foundedRaw = $foundedRaw !== null && trim((string) $foundedRaw) !== '' ? trim((string) $foundedRaw) : null;
            $foundedParsed = $intake->parseFoundedYearString($foundedRaw);
            if ($foundedParsed['error'] !== null) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
                    'messages' => [$foundedParsed['error']],
                ];

                continue;
            }
            $establishedYear = $foundedParsed['year'];

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
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
                    'messages' => $validator->errors()->all(),
                ];

                continue;
            }

            $dupMsg = $intake->duplicateRowMessage($website, $name, $country);
            if ($dupMsg !== null) {
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
                    'messages' => [$dupMsg],
                ];

                continue;
            }

            try {
                $slug = $intake->uniqueSlugForName($name);

                $casino = Casino::create([
                    'name' => $name,
                    'slug' => $slug,
                    'country' => $country,
                    'country_slug' => Str::slug($country),
                    'region' => $region,
                    'locality' => $locality,
                    'website' => $website,
                    'social_links' => Casino::mergeSocialLinks(null, $linkedin),
                    'established_year' => $establishedYear,
                ]);
                $casino->status = 'published';
                $casino->enrichment_status = 'pending';
                $casino->save();

                $enrichmentService->createEnrichmentJobs($casino);
                $imported++;
            } catch (\Throwable $e) {
                report($e);
                $errors[] = [
                    'row' => $rowNumber,
                    'data' => $this->importErrorData($name, $country, $website, $region, $locality, $linkedin, $foundedParsed['raw']),
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
        foreach (['linkedin', 'linkedin_url', 'social_linkedin'] as $col) {
            $i = array_search($col, $header, true);
            if ($i !== false) {
                return $i;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $header
     */
    private function foundedColumnIndex(array $header): int|false
    {
        foreach (['founded', 'established_year', 'year_founded'] as $col) {
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
     * @return array{name: string, country: string, website: ?string, region: ?string, locality: ?string, linkedin: ?string, founded: ?string}
     */
    private function importErrorData(
        string $name,
        string $country,
        ?string $website,
        ?string $region,
        ?string $locality,
        ?string $linkedin,
        ?string $founded = null,
    ): array {
        return [
            'name' => $name,
            'country' => $country,
            'website' => $website,
            'region' => $region,
            'locality' => $locality,
            'linkedin' => $linkedin,
            'founded' => $founded,
        ];
    }
}
