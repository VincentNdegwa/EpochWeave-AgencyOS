<?php

namespace App\Enums;

enum TaxType: string
{
    case None = 'none';
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
}
