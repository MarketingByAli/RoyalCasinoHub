<?php

namespace App\Betting\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BettingAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $alertType,
        public array $data
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->alertType) {
            'bet_accepted' => 'Your challenge was accepted',
            'result_published' => 'Result published for your challenge',
            'bet_settled' => 'Challenge settled',
            'dispute_opened' => 'Dispute opened on a challenge',
            'dispute_resolved' => 'Dispute resolved',
            default => 'Challenge update',
        };

        $mail = (new MailMessage)
            ->subject($subject.' | '.config('app.name'))
            ->greeting('Hello '.($notifiable->bettingProfile?->display_name ?? $notifiable->name));

        $title = $this->data['market_title'] ?? 'Challenge';

        $mail->line($this->bodyLine());

        if (! empty($this->data['market_id'])) {
            $mail->action('View challenge', url('/challenges/'.$this->data['market_id']));
        }

        return $mail->line('Play-money only — no real cash value.');
    }

    private function bodyLine(): string
    {
        return match ($this->alertType) {
            'bet_accepted' => ($this->data['challenger'] ?? 'Someone').' accepted your challenge: '.$this->data['market_title'],
            'result_published' => 'Result for "'.$this->data['market_title'].'": '.($this->data['winning_outcome'] ?? 'published'),
            'bet_settled' => 'Challenge "'.$this->data['market_title'].'" settled. You '.($this->data['result'] ?? 'participated').'.',
            'dispute_opened' => 'A dispute was opened on "'.$this->data['market_title'].'" by '.($this->data['opened_by'] ?? 'a participant').'.',
            'dispute_resolved' => 'Dispute on "'.$this->data['market_title'].'" resolved: '.($this->data['resolution'] ?? 'closed'),
            default => 'Update for challenge: '.($this->data['market_title'] ?? ''),
        };
    }
}
