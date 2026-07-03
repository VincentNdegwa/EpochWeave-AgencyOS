<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalSent extends Notification
{
    use Queueable;

    public function __construct(
        public Proposal $proposal
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $publicUrl = route('proposals.public.show', $this->proposal->token);

        return (new MailMessage)
            ->subject('Proposal: '.$this->proposal->title)
            ->greeting('Hello '.$notifiable->first_name.',')
            ->line('You have been sent a proposal to review.')
            ->line('Proposal: '.$this->proposal->title)
            ->line('You can view the proposal by clicking the link below:')
            ->action('View Proposal', $publicUrl)
            ->line('This link will remain active and you can return to review the proposal at any time.')
            ->salutation('Thank you for your consideration.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'proposal_title' => $this->proposal->title,
            'proposal_token' => $this->proposal->token,
            'sent_at' => $this->proposal->sent_at,
        ];
    }
}
