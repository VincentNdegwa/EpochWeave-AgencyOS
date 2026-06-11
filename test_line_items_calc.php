<?php

// Simple test to verify line items calculation
$testItems = [
    [
        'unit_price' => 1000,
        'quantity' => 1,
        'item_discount_type' => 'none',
        'item_discount_value' => 0,
    ],
    [
        'unit_price' => 1500,
        'quantity' => 3,
        'item_discount_type' => 'none',
        'item_discount_value' => 0,
    ]
];

$subtotal = 0;
foreach ($testItems as $item) {
    $itemSubtotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 1);
    $subtotal += $itemSubtotal;
}

echo "Expected subtotal: " . $subtotal . "\n";
echo "Item 1: 1000 x 1 = 1000\n";
echo "Item 2: 1500 x 3 = 4500\n";
echo "Total: 1000 + 4500 = 5500\n";
