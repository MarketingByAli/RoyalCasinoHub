<?php

namespace App\Betting\Services;

use App\Betting\Models\UserReport;
use App\Models\User;

class MarketModerationService
{
    public function __construct(
        private MarketReviewService $keywordReview,
    ) {}

    /**
     * @return array{passed: bool, flags: list<string>, requires_manual_review: bool}
     */
    public function review(string $title, string $description, array $outcomeOptions, ?User $creator = null): array
    {
        $base = $this->keywordReview->review($title, $description, $outcomeOptions);
        $flags = $base['flags'];

        $haystack = strtolower($title.' '.$description.' '.implode(' ', $outcomeOptions));

        if (preg_match('/https?:\/\/|www\./i', $haystack)) {
            $flags[] = 'link_spam';
        }

        if (preg_match('/(.)\1{6,}/', $haystack)) {
            $flags[] = 'spam_repeat_chars';
        }

        if ($creator) {
            $openReports = UserReport::query()
                ->where('reported_id', $creator->id)
                ->where('status', 'open')
                ->count();

            if ($openReports >= 3) {
                $flags[] = 'reporter_signals:open_reports';
            }
        }

        $requiresManual = count($flags) > 0;

        return [
            'passed' => ! $requiresManual,
            'flags' => array_values(array_unique($flags)),
            'requires_manual_review' => $requiresManual,
        ];
    }
}
