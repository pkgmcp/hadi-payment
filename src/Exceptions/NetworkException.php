<?php

declare(strict_types=1);

namespace Hadi\Payment\Exceptions;

/**
 * Exception thrown when network communication fails
 */
class NetworkException extends PaymentException
{
    public function __construct(string $message = 'Network communication failed', array $context = [])
    {
        parent::__construct($message, 0, null, $context);
    }
}
