<?php

return [
    'defaults' => [
        'proposals' => [
            'default_validity_days' => 14,
            'auto_archive' => true,
            'default_deposit_percentage' => 50,
            'payment_due_days' => 7,
            'default_terms' => "# Master Services Agreement\n1. Scope of Work...\n2. Payment Schedule...",
            'sender_name' => 'Vincent Ndegwa',
            'sender_title' => 'Director of Engineering',
            'numbering' => [
                'format' => '{PREFIX}{YEAR}{DELIMITER}{SEQUENCE}',
                'prefix' => 'PROP',
                'delimiter' => '-',
                'sequence_padding' => 4,
                'next_sequence_number' => 1,
            ],
        ],
        'notifications' => [
            'proposals' => [
                'notify_on_view' => true,
                'notify_on_sign' => true,
                'send_pdf_on_sign' => true,
                'send_expiry_reminders' => true,
                'expiry_reminder_days_before' => 3,
            ],
        ],
    ],
];
