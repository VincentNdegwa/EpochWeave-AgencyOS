<?php

namespace App\Enums;

enum TaxType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
}
