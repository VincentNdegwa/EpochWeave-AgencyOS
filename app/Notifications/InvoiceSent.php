<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceSent extends Notification
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $publicUrl = route('invoices.public.show', $this->invoice->token);

        return (new MailMessage)
            ->subject('Invoice: '.($this->invoice->invoice_number ?? 'INV-'.$this->invoice->id))
            ->greeting('Hello '.$notifiable->first_name.',')
            ->line('You have been sent an invoice to review.')
            ->line('Invoice: '.($this->invoice->invoice_number ?? 'INV-'.$this->invoice->id))
            ->line('You can view the invoice by clicking the link below:')
            ->action('View Invoice', $publicUrl)
            ->line('This link will remain active and you can return to review the invoice at any time.')
            ->salutation('Thank you for your business.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'invoice_token' => $this->invoice->token,
            'sent_at' => $this->invoice->sent_at,
        ];
    }
}
