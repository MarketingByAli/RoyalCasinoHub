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
        // Persistence is handled by BettingNotificationService; mail is the outbound channel.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->alertType) {
            'bet_accepted' => 'Your challenge was accepted',
            'challenge_joined' => 'Someone joined your challenge',
            'result_published' => 'Result published for your challenge',
            'bet_settled' => 'Challenge settled',
            'dispute_opened' => 'Dispute opened on a challenge',
            'dispute_resolved' => 'Dispute resolved',
            'counter_offer_received' => 'Counter-offer received',
            'counter_offer_accepted' => 'Counter-offer accepted',
            'referral_bonus' => 'Referral bonus credited',
            'faucet_claimed' => 'Daily faucet claimed',
            'rg_limit_hit' => 'Stake limit reached',
            default => 'Challenge update',
        };

        $mail = (new MailMessage)
            ->subject($subject.' | '.config('app.name'))
            ->greeting('Hello '.($notifiable->bettingProfile?->display_name ?? $notifiable->name));

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
            'challenge_joined' => ($this->data['joiner'] ?? 'Someone').' joined "'.$this->data['market_title'].'"',
            'result_published' => 'Result for "'.$this->data['market_title'].'": '.($this->data['winning_outcome'] ?? 'published'),
            'bet_settled' => 'Challenge "'.$this->data['market_title'].'" settled. You '.($this->data['result'] ?? 'participated').'.',
            'dispute_opened' => 'A dispute was opened on "'.$this->data['market_title'].'" by '.($this->data['opened_by'] ?? 'a participant').'.',
            'dispute_resolved' => 'Dispute on "'.$this->data['market_title'].'" resolved: '.($this->data['resolution'] ?? 'closed'),
            'counter_offer_received' => ($this->data['from'] ?? 'Someone').' proposed '.$this->data['proposed_stake'].' pts on "'.$this->data['market_title'].'"',
            'counter_offer_accepted' => 'Your counter-offer on "'.$this->data['market_title'].'" was accepted.',
            'referral_bonus' => 'You received a referral bonus of '.$this->data['amount'].' pts.',
            'faucet_claimed' => 'You claimed '.$this->data['amount'].' faucet points.',
            'rg_limit_hit' => 'You hit your '.($this->data['limit'] ?? 'stake').' limit.',
            default => 'Update for challenge: '.($this->data['market_title'] ?? ''),
        };
    }
}
