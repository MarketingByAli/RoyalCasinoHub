<?php

namespace App\Services;

use App\Models\Casino;
use App\Models\Review;
use App\Models\SeoSetting;
use Illuminate\Support\Str;

class SeoService
{
    public function getMetaTitle(?string $pageTitle, ?Casino $casino = null): string
    {
        if ($pageTitle) {
            return $this->appendSiteName($pageTitle);
        }
        if ($casino) {
            $pattern = SeoSetting::get('meta_title_pattern', '{Casino Name} Review {Year} — Bonuses, Games & Rating | {Site Name}');

            return $this->replacePattern($pattern, $casino);
        }

        return SeoSetting::get('meta_title_default', 'RoyalCasinoHub — Trusted Online Casino Reviews & Ratings');
    }

    public function getMetaDescription(?string $pageDescription, ?Casino $casino = null): string
    {
        if ($pageDescription) {
            return Str::limit($pageDescription, 160);
        }
        if ($casino) {
            $pattern = SeoSetting::get('meta_description_pattern', 'Read our {Casino Name} review. Honest ratings, bonuses, games & more. Updated {Year}.');

            return Str::limit($this->replacePattern($pattern, $casino), 160);
        }

        return SeoSetting::get('meta_description_default', 'Discover trusted online casino reviews, ratings, and bonuses. Find the best casinos for your country.');
    }

    public function getCanonicalUrl(?string $customUrl, string $fallbackUrl): string
    {
        if ($customUrl) {
            return $this->ensureAbsoluteUrl($customUrl);
        }

        return $this->ensureAbsoluteUrl($fallbackUrl);
    }

    public function getRobots(?string $pageRobots): string
    {
        if ($pageRobots) {
            return $pageRobots;
        }

        return 'index,follow';
    }

    public function generateCasinoSchema(Casino $casino, ?iterable $reviewsForSchema = null): array
    {
        $url = url("/casino/{$casino->slug}");
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => ['Organization', 'LocalBusiness'],
            'name' => $casino->name,
            'url' => $casino->website ?? $url,
            'description' => $casino->meta_description ?? $casino->short_description ?? Str::limit((string) $casino->description, 200),
        ];

        $schemaImage = $casino->logo_url ?: $casino->effectiveScreenshotUrl();
        if ($schemaImage) {
            $schema['image'] = $schemaImage;
        }

        if ($casino->country) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'addressCountry' => $casino->country,
            ];
        }

        if ($casino->reviews_count > 0 && $casino->average_rating) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $casino->average_rating, 1),
                'reviewCount' => (int) $casino->reviews_count,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        $reviewsForSchema = $reviewsForSchema ?? (
            $casino->relationLoaded('approvedReviews')
                ? $casino->approvedReviews->take(10)
                : collect()
        );

        $reviewNodes = [];
        foreach ($reviewsForSchema as $review) {
            if (! $review instanceof Review || $review->status !== 'approved') {
                continue;
            }
            $authorName = $review->relationLoaded('user') && $review->user
                ? $review->user->name
                : 'Player';
            $reviewNodes[] = [
                '@type' => 'Review',
                'author' => [
                    '@type' => 'Person',
                    'name' => $authorName,
                ],
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (int) $review->rating,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ],
                'reviewBody' => Str::limit(strip_tags((string) $review->content), 5000),
                'datePublished' => $review->created_at?->toIso8601String(),
                'itemReviewed' => [
                    '@type' => 'Organization',
                    'name' => $casino->name,
                    'url' => $url,
                ],
            ];
        }
        if ($reviewNodes !== []) {
            $schema['review'] = $reviewNodes;
        }

        if ($casino->email) {
            $schema['email'] = $casino->email;
        }

        if ($casino->phone) {
            $schema['telephone'] = $casino->phone;
        }

        $sameAs = array_values(array_filter($casino->social_links ?? []));
        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    public function generateBreadcrumbSchema(array $items): array
    {
        $listItems = [];
        $position = 1;
        foreach ($items as $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }

    private function replacePattern(string $pattern, Casino $casino): string
    {
        $replacements = [
            '{Casino Name}' => $casino->name,
            '{Year}' => (string) now()->year,
            '{Site Name}' => SeoSetting::get('site_name', 'RoyalCasinoHub'),
        ];
        $result = str_replace(array_keys($replacements), array_values($replacements), $pattern);

        return $this->appendSiteName($result);
    }

    private function appendSiteName(string $title): string
    {
        $siteName = SeoSetting::get('site_name', 'RoyalCasinoHub');
        if (! Str::contains($title, $siteName)) {
            $title .= ' | '.$siteName;
        }

        return $title;
    }

    private function ensureAbsoluteUrl(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }
}
