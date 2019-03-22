<?php

declare(strict_types=1);

namespace JDecool\Whois\Exception;

class RuntimeException extends \RuntimeException implements Exception
{
    public static function fromSocketException($resource): self
    {
        $code = socket_last_error($resource);
        $string = socket_strerror($code);
        socket_clear_error($resource);

        return new self("Socket operation failed: $string", $code);
    }
}
