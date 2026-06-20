<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            color: #1f2937;
            background: #fff;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e5e7eb;
        }
        .header h1 { font-size: 24px; font-weight: 700; }
        .header .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: {{ $invoice->invoiceStatus?->color ?? '#6b7280' }};
            color: #fff;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        .meta-group h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .meta-group p { font-size: 14px; }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table th {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 2px solid #e5e7eb;
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .items-table .num { text-align: right; }
        .totals {
            width: 300px;
            margin-left: auto;
            margin-bottom: 32px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .totals-row.total {
            border-top: 2px solid #e5e7eb;
            font-weight: 700;
            font-size: 16px;
        }
        .payments {
            margin-bottom: 32px;
        }
        .payments h3 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>RECEIPT</h1>
            <p style="font-size: 14px; color: #6b7280; margin-top: 4px;">
                #{{ $invoice->invoice_number }}
            </p>
        </div>
        <span class="badge">{{ $invoice->invoiceStatus?->title ?? 'Paid' }}</span>
    </div>

    <div class="meta-grid">
        <div class="meta-group">
            <h3>From</h3>
            <p><strong>{{ $invoice->workspace?->name ?? 'Company' }}</strong></p>
            @if($invoice->workspace?->email)
                <p>{{ $invoice->workspace->email }}</p>
            @endif
        </div>
        <div class="meta-group">
            <h3>To</h3>
            <p><strong>{{ $invoice->account?->company_name ?? 'Client' }}</strong></p>
            @if($invoice->accountContact)
                <p>{{ $invoice->accountContact->first_name }} {{ $invoice->accountContact->last_name }}</p>
                <p>{{ $invoice->accountContact->email }}</p>
            @endif
        </div>
        <div class="meta-group">
            <h3>Issue Date</h3>
            <p>{{ $invoice->issue_date?->format('F j, Y') ?? '—' }}</p>
        </div>
        <div class="meta-group">
            <h3>Paid Date</h3>
            <p>{{ $invoice->paid_at?->format('F j, Y') ?? '—' }}</p>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th class="num">Qty</th>
                <th class="num">Price</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->item_name }}</strong>
                    @if($item->description)
                        <br><span style="color: #6b7280; font-size: 12px;">{{ $item->description }}</span>
                    @endif
                </td>
                <td class="num">{{ $item->quantity }}</td>
                <td class="num">{{ number_format($item->unit_price, 2) }}</td>
                <td class="num">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span>{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        @if($invoice->discount_total > 0)
        <div class="totals-row">
            <span>Discount</span>
            <span>-{{ number_format($invoice->discount_total, 2) }}</span>
        </div>
        @endif
        @if($invoice->total_tax_amount > 0)
        <div class="totals-row">
            <span>Tax</span>
            <span>{{ number_format($invoice->total_tax_amount, 2) }}</span>
        </div>
        @endif
        <div class="totals-row total">
            <span>Total</span>
            <span>{{ number_format($invoice->grand_total, 2) }}</span>
        </div>
        <div class="totals-row" style="color: #16a34a;">
            <span>Paid</span>
            <span>{{ number_format($invoice->amount_paid, 2) }}</span>
        </div>
    </div>

    @if($invoice->payments->isNotEmpty())
    <div class="payments">
        <h3>Payment History</h3>
        @foreach($invoice->payments as $payment)
        <div class="payment-row">
            <span>{{ $payment->paid_at?->format('F j, Y') }} — {{ ucwords(str_replace('_', ' ', $payment->method)) }}</span>
            <span>{{ number_format($payment->amount, 2) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business.</p>
        @if($invoice->notes)
            <p style="margin-top: 8px; font-style: italic;">{{ $invoice->notes }}</p>
        @endif
    </div>

    <div class="no-print" style="text-align: center; margin-top: 24px;">
        <button onclick="window.print()" style="padding: 8px 24px; cursor: pointer;">Print / Save as PDF</button>
    </div>
</body>
</html>
