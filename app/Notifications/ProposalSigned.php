<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalSigned extends Notification
{
    use Queueable;

    public function __construct(
        public Proposal $proposal,
        public $signer = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $signerName = $this->getSignerName();
        $url = $notifiable instanceof \App\Models\User 
            ? route('proposals.show', $this->proposal)
            : route('proposals.public.show', $this->proposal->token);

        return (new MailMessage)
            ->subject('Proposal SIGNED: ' . $this->proposal->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line("🎉 Excellent news! {$signerName} has digitally signed your proposal!")
            ->line('Proposal: ' . $this->proposal->title)
            ->line('The proposal has been accepted and is now legally binding.')
            ->line('This is the perfect time to set up the project and send any initial invoices.')
            ->action('View Signed Proposal', $url)
            ->line('You can now proceed with the next steps in your workflow.')
            ->salutation('Congratulations on the successful proposal!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'proposal_title' => $this->proposal->title,
            'proposal_token' => $this->proposal->token,
            'signer_id' => $this->signer?->id,
            'signer_type' => $this->signer ? get_class($this->signer) : null,
            'signer_name' => $this->getSignerName(),
            'signed_at' => $this->proposal->signed_at,
            'title' => 'Proposal Signed',
            'message' => "🎉 Excellent news! {$this->getSignerName()} has digitally signed your proposal \"{$this->proposal->title}\"!",
            'icon' => 'mdi:check-circle',
            'color' => 'green-500',
        ];
    }

    private function getSignerName(): string
    {
        if (!$this->signer) {
            return 'A client';
        }

        if (method_exists($this->signer, 'first_name')) {
            return $this->signer->first_name;
        }

        if (method_exists($this->signer, 'name')) {
            return $this->signer->name();
        }

        return 'A client';
    }
}
