<?php

declare(strict_types=1);

namespace JDecool\Whois\Exception;

use RuntimeException;
use Throwable;

class InvalidDomain extends RuntimeException implements Exception
{
    public function __construct(string $domain, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("The domain '$domain' is invalid.", $code, $previous);
    }
}
