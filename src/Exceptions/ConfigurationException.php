<?php

declare(strict_types=1);

namespace Hadi\Payment\Exceptions;

/**
 * Exception thrown when payment gateway configuration is invalid
 */
class ConfigurationException extends PaymentException
{
    public function __construct(string $message = 'Invalid payment gateway configuration', array $context = [])
    {
        parent::__construct($message, 0, null, $context);
    }
}
