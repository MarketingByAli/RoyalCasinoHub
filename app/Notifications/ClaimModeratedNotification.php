<?php

namespace App\Notifications;

use App\Models\ClaimedListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimModeratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ClaimedListing $claim,
        public string $action
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $casinoName = $this->claim->casino->name;
        $line = match ($this->action) {
            'approved' => "Your claim for {$casinoName} has been approved. You can now manage this listing.",
            'rejected' => "Your claim for {$casinoName} was not approved.".($this->claim->notes ? ' See notes in your account.' : ''),
            default => "Your claim for {$casinoName} has been updated.",
        };

        return (new MailMessage)
            ->subject(
                $this->action === 'approved'
                    ? 'Your listing claim was approved — RoyalCasinoHub'
                    : 'Your listing claim was not approved — RoyalCasinoHub'
            )
            ->line($line)
            ->action('View your claims', route('account.claims'))
            ->line('If you have questions, reply to this email or contact support.');
    }
}
