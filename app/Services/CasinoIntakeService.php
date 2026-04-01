<?php

namespace App\Services;

use App\Models\Casino;
use Illuminate\Support\Str;

class CasinoIntakeService
{
    public function normalizeImportUrl(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }
        if (Str::startsWith($value, '//')) {
            return 'https:'.$value;
        }

        return 'https://'.$value;
    }

    /**
     * @return array{year: ?int, error: ?string, raw: ?string}
     */
    public function parseFoundedYearString(?string $raw): array
    {
        if ($raw === null) {
            return ['year' => null, 'error' => null, 'raw' => null];
        }
        $raw = trim($raw);
        if ($raw === '') {
            return ['year' => null, 'error' => null, 'raw' => null];
        }
        if (! is_numeric($raw)) {
            return ['year' => null, 'error' => 'Founded must be a valid year.', 'raw' => $raw];
        }
        $y = (int) round((float) str_replace([',', ' ', "\xc2\xa0"], '', $raw));
        if ($y < 1900 || $y > 2100) {
            return ['year' => null, 'error' => 'Founded must be between 1900 and 2100.', 'raw' => $raw];
        }

        return ['year' => $y, 'error' => null, 'raw' => $raw];
    }

    public function duplicateRowMessage(?string $website, string $name, string $country): ?string
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

    public function uniqueSlugForName(string $name): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug ?: 'casino';
        $counter = 1;
        while (Casino::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
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
