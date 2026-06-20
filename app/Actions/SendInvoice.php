<?php

namespace App\Actions;

use App\Models\AccountContact;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Notifications\InvoiceSent;

class SendInvoice
{
    public function send(Invoice $invoice): Invoice
    {
        $invoice->loadMissing(['account', 'accountContact']);

        $contact = $this->resolveContact($invoice);

        if (! $contact) {
            throw new \InvalidArgumentException('Invoice must have an account with at least one contact to send.');
        }

        if (! $invoice->token) {
            throw new \InvalidArgumentException('Invoice must have a token to generate a public link.');
        }

        $sentStatus = InvoiceStatus::where('workspace_id', $invoice->workspace_id)
            ->where('automation_trigger', 'sent')
            ->first();

        $invoice->update([
            'invoice_status_id' => $sentStatus?->id,
            'sent_at' => now(),
        ]);

        $contact->notify(new InvoiceSent($invoice));

        return $invoice->fresh();
    }

    private function resolveContact(Invoice $invoice): ?AccountContact
    {
        if ($invoice->account_contact_id && $invoice->accountContact) {
            return $invoice->accountContact;
        }

        $account = $invoice->account;

        if (! $account) {
            return null;
        }

        $contacts = $account->contacts()->get();

        $billingContact = $contacts->firstWhere('receives_billing', true);
        if ($billingContact) {
            return $billingContact;
        }

        $primaryContact = $contacts->firstWhere('is_primary', true);
        if ($primaryContact) {
            return $primaryContact;
        }

        return $contacts->first();
    }
}
