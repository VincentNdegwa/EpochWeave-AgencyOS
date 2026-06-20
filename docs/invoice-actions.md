















Here's the discovery and plan for the 6 dead-emit actions in [InvoiceActions.vue](cci:7://file://wsl.localhost/Ubuntu/home/vincent/EpochWeave-AgencyOS/resources/js/pages/invoices/components/InvoiceActions.vue:0:0-0:0).

## Discovery

All 6 emits fire into the void — no parent ([show.vue](cci:7://file://wsl.localhost/Ubuntu/home/vincent/EpochWeave-AgencyOS/resources/js/pages/invoices/show.vue:0:0-0:0), `index.vue`, [KanbanView.vue](cci:7://file://wsl.localhost/Ubuntu/home/vincent/EpochWeave-AgencyOS/resources/js/pages/invoices/KanbanView.vue:0:0-0:0)) listens to any of them.

| Action | Emit | Backend Exists? | Dialog Exists? |
|---|---|---|---|
| Record Payment | `record-payment` | **Yes** — [PaymentController::store](cci:1://file://wsl.localhost/Ubuntu/home/vincent/EpochWeave-AgencyOS/app/Http/Controllers/InvoiceController.php:64:4-107:5) + [PaymentService::recordPayment](cci:1://file://wsl.localhost/Ubuntu/home/vincent/EpochWeave-AgencyOS/app/Services/PaymentService.php:16:4-39:5) | **No** |
| Download Receipt | `download-receipt` | **No** | **No** |
| Record Refund | `record-refund` | **No** | **No** |
| Duplicate / Duplicate to Draft | `duplicate` | **No** | **No** |
| Send Late Reminder | `send-reminder` | **No** | **No** |
| View History Audit | `view-history` | N/A (display only) | **No** |

---

## Implementation Plan

### 1. Record Payment
- **Dialog** — `RecordPaymentDialog.vue` (amount, method select, paid_at date, reference, notes)
- **Backend** — Already wired at `POST /invoices/{invoice}/payments` via [PaymentController::store](cci:1://file://wsl.localhost/Ubuntu/home/vincent/EpochWeave-AgencyOS/app/Http/Controllers/InvoiceController.php:64:4-107:5)
- **Action change** — Replace `emit('record-payment')` with `dialogOpen = true`

### 2. Download Receipt
- **Backend** — Add `InvoiceController::downloadReceipt(int $id)` that returns a PDF stream (reuse `InvoiceDocument.vue` HTML → PDF via `laravel-dompdf` or similar, or just stream the public invoice view)
- **Action change** — Replace `emit('download-receipt')` with `window.open(InvoiceController.downloadReceipt(invoice.id).url, '_blank')`

### 3. Duplicate / Duplicate to Draft
- **Backend** — Add `InvoiceController::duplicate(int $id)` + `InvoiceService::duplicateInvoice()` that clones the invoice, resets dates, sets status to draft, clones line items
- **Action change** — Confirmation dialog via `useConfirmation` (already used for delete/void), then `router.post(duplicateUrl)` — no custom dialog needed

### 4. Send Late Reminder
- **Backend** — Add `InvoiceController::sendReminder(int $id)` that queues an email to the invoice contact
- **Action change** — Confirmation dialog then `router.post(reminderUrl)` — no custom dialog needed

### 5. Record Refund / Issue Credit Note
- **Backend** — New `CreditNote` model + migration, `CreditNoteController`, `CreditNoteService` (refund amount, reason, date, link to invoice & payment). Also updates `amount_paid` and potentially reverts status.
- **Dialog** — `RecordRefundDialog.vue` (amount, reason, refunded_at)
- **This is the heaviest lift.** Recommend tackling last.

### 6. View History Audit
- **Backend** — Already have `Activity` records loaded in [show.vue](cci:7://file://wsl.localhost/Ubuntu/home/vincent/EpochWeave-AgencyOS/resources/js/pages/invoices/show.vue:0:0-0:0)
- **Dialog** — `InvoiceHistoryDialog.vue` displaying the existing `activities` prop (or a dedicated `/invoices/{id}/history` endpoint if more detail needed)
- **Action change** — `dialogOpen = true` instead of emit

---

## Recommended Execution Order

1. **Record Payment** — backend done, just needs dialog
2. **Duplicate / Duplicate to Draft** — lightweight backend clone + confirmation
3. **Send Late Reminder** — lightweight backend email + confirmation
4. **Download Receipt** — medium backend PDF work
5. **View History Audit** — pure UI dialog
6. **Record Refund** — new model + migration + service + dialog (complex)

Want me to start with **Record Payment**?