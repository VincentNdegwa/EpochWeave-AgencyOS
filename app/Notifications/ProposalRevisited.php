<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalRevisited extends Notification
{
    use Queueable;

    public function __construct(
        public Proposal $proposal,
        public $viewer = null,
        public ?\DateTime $lastViewedAt = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $viewerName = $this->getViewerName();
        $url = $notifiable instanceof \App\Models\User 
            ? route('proposals.show', $this->proposal)
            : route('proposals.public.show', $this->proposal->token);
        $timeSinceLastView = $this->lastViewedAt 
            ? $this->lastViewedAt->diffForHumans(now()) 
            : 'recently';

        return (new MailMessage)
            ->subject('Proposal Revisited: ' . $this->proposal->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line("{$viewerName} has returned to review your proposal again!")
            ->line('Proposal: ' . $this->proposal->title)
            ->line("They previously viewed it {$timeSinceLastView}, and now they're back.")
            ->line('This signals renewed interest or internal client deliberation.')
            ->action('View Proposal', $url)
            ->line('This could be the perfect opportunity to address any questions or concerns.')
            ->salutation('Now might be a great time to follow up!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'proposal_title' => $this->proposal->title,
            'proposal_token' => $this->proposal->token,
            'viewer_id' => $this->viewer?->id,
            'viewer_type' => $this->viewer ? get_class($this->viewer) : null,
            'viewer_name' => $this->getViewerName(),
            'revisited_at' => now(),
            'last_viewed_at' => $this->lastViewedAt,
            'title' => 'Proposal Revisited',
            'message' => "{$this->getViewerName()} returned to review your proposal \"{$this->proposal->title}\" again.",
            'icon' => 'mdi:refresh',
            'color' => 'amber-500',
        ];
    }

    private function getViewerName(): string
    {
        if (!$this->viewer) {
            return 'A client';
        }

        if (method_exists($this->viewer, 'first_name')) {
            return $this->viewer->first_name;
        }

        if (method_exists($this->viewer, 'name')) {
            return $this->viewer->name();
        }

        return 'A client';
    }
}
