<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceLateReminder extends Notification
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
            ->subject('Reminder: Invoice '.($this->invoice->invoice_number ?? 'INV-'.$this->invoice->id).' is overdue')
            ->greeting('Hello '.$notifiable->first_name.',')
            ->line('This is a friendly reminder that the following invoice is now overdue:')
            ->line('Invoice: '.($this->invoice->invoice_number ?? 'INV-'.$this->invoice->id))
            ->line('Due Date: '.($this->invoice->due_date?->format('F j, Y') ?? 'N/A'))
            ->action('View & Pay Invoice', $publicUrl)
            ->line('If you have already sent payment, please disregard this message.')
            ->salutation('Thank you for your business.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'invoice_token' => $this->invoice->token,
        ];
    }
}
