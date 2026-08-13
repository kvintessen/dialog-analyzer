<?php

namespace App\Enums;

enum DialogResult: string
{
    case Purchased = 'purchased';
    case NotPurchased = 'not_purchased';
    case Undecided = 'undecided';
}
