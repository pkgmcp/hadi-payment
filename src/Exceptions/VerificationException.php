<?php

namespace Hadi\Payment\Exceptions;

class VerificationException extends PaymentException
{
    public function __construct(string $message = "Payment verification failed.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
