<?php

namespace App\Betting\Services;

class MarketReviewService
{
    /**
     * @return array{passed: bool, flags: list<string>, requires_manual_review: bool}
     */
    public function review(string $title, string $description, array $outcomeOptions): array
    {
        $flags = [];
        $haystack = strtolower($title.' '.$description.' '.implode(' ', $outcomeOptions));

        foreach (config('betting.prohibited_keywords', []) as $keyword) {
            if (str_contains($haystack, strtolower($keyword))) {
                $flags[] = 'prohibited_keyword:'.$keyword;
            }
        }

        if (preg_match('/\b\d{3}[-.\s]?\d{3}[-.\s]?\d{4}\b/', $haystack)) {
            $flags[] = 'personal_info:phone';
        }

        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/', $haystack)) {
            $flags[] = 'personal_info:email';
        }

        $requiresManual = count($flags) > 0;

        return [
            'passed' => ! $requiresManual,
            'flags' => $flags,
            'requires_manual_review' => $requiresManual,
        ];
    }
}
