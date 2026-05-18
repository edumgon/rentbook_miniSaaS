<?php

namespace App\Domain\ValueObject;

/**
 * Loan Status Value Object
 */
enum LoanStatus: string
{
    case ACTIVE = 'active';
    case RETURNED = 'returned';
}
