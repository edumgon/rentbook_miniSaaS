<?php

namespace App\Domain\ValueObject;

/**
 * Book Status Value Object
 * 
 * Represents the possible states of a book.
 */
enum BookStatus: string
{
    case AVAILABLE = 'available';
    case LENT = 'lent';
}
