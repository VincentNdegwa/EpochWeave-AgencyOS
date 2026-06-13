<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalDeclined extends Notification
{
    use Queueable;

    public function __construct(
        public Proposal $proposal,
        public $decliner = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $declinerName = $this->getDeclinerName();
        $url = $notifiable instanceof \App\Models\User 
            ? route('proposals.show', $this->proposal)
            : route('proposals.public.show', $this->proposal->token);
        $declineReason = $this->proposal->decline_reason;

        return (new MailMessage)
            ->subject('Proposal Declined: ' . $this->proposal->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line("{$declinerName} has declined your proposal.")
            ->line('Proposal: ' . $this->proposal->title)
            
            ->when($declineReason, function ($message) use ($declineReason) {
                $message->line('Reason provided:')
                    ->line('"' . $declineReason . '"');
            })
            
            ->line('This feedback can help you improve future proposals.')
            ->action('View Declined Proposal', $url)
            ->line('Consider reaching out to understand their needs better or offer alternative solutions.')
            ->salutation('Don\'t be discouraged - every no brings you closer to a yes!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'proposal_title' => $this->proposal->title,
            'proposal_token' => $this->proposal->token,
            'decliner_id' => $this->decliner?->id,
            'decliner_type' => $this->decliner ? get_class($this->decliner) : null,
            'decliner_name' => $this->getDeclinerName(),
            'declined_at' => $this->proposal->updated_at,
            'decline_reason' => $this->proposal->decline_reason,
            'title' => 'Proposal Declined',
            'message' => "{$this->getDeclinerName()} declined your proposal \"{$this->proposal->title}\".",
            'icon' => 'mdi:close-circle',
            'color' => 'red-500',
        ];
    }

    private function getDeclinerName(): string
    {
        if (!$this->decliner) {
            return 'A client';
        }

        if (method_exists($this->decliner, 'first_name')) {
            return $this->decliner->first_name;
        }

        if (method_exists($this->decliner, 'name')) {
            return $this->decliner->name();
        }

        return 'A client';
    }
}
