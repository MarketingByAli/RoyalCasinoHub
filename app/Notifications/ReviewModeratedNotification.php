<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewModeratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Review $review,
        public string $action
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $casinoName = $this->review->casino->name;
        $line = match ($this->action) {
            'approved' => "Your review of {$casinoName} has been approved and is now visible on the site.",
            'rejected' => "Your review of {$casinoName} was not approved.",
            default => "Your review of {$casinoName} has been updated.",
        };

        return (new MailMessage)
            ->subject(
                $this->action === 'approved'
                    ? 'Your review was approved — RoyalCasinoHub'
                    : 'Your review was not approved — RoyalCasinoHub'
            )
            ->line($line)
            ->action('View your reviews', route('account.reviews'))
            ->line('Thank you for contributing to RoyalCasinoHub.');
    }
}
