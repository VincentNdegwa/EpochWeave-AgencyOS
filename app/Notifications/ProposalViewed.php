<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalViewed extends Notification
{
    use Queueable;

    public function __construct(
        public Proposal $proposal,
        public $viewer = null
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

        return (new MailMessage)
            ->subject('Proposal Viewed: ' . $this->proposal->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line("Great news! {$viewerName} has just viewed your proposal for the first time.")
            ->line('Proposal: ' . $this->proposal->title)
            ->line('This is the perfect time to follow up while they actively considering your offer.')
            ->action('View Proposal', $url)
            ->line('The client can return to review the proposal at any time using their unique link.')
            ->salutation('Good luck with your follow-up!');
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
            'viewed_at' => now(),
            'title' => 'Proposal Viewed',
            'message' => "{$this->getViewerName()} viewed your proposal \"{$this->proposal->title}\" for the first time.",
            'icon' => 'mdi:eye',
            'color' => 'blue-500',
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
