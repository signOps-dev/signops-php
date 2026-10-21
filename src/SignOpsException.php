<?php

declare(strict_types=1);

namespace SignOps;

/** An error from the API, with its HTTP status and the documented code (invalid_request, not_allowed, ...). */
final class SignOpsException extends \RuntimeException
{
    public function __construct(public readonly int $status, public readonly string $errorCode, string $message)
    {
        parent::__construct($message, $status);
    }
}
