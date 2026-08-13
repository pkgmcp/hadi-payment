<?php

declare(strict_types=1);

namespace Hadi\Payment\Exceptions;

use Exception;
use Throwable;

/**
 * Base exception for payment gateway errors
 */
class PaymentException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?array $context = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get exception context data
     */
    public function getContext(): ?array
    {
        return $this->context;
    }
}
