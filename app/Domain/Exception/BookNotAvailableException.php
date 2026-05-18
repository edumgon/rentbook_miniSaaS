<?php

namespace App\Domain\Exception;

/**
 * Exception thrown when trying to lend a book that is not available
 */
class BookNotAvailableException extends \DomainException
{
}
