<?php

declare(strict_types=1);

namespace JDecool\Whois;

use JDecool\Whois\Exception\RuntimeException;
use Socket as PhpSocket;

final class Socket
{
    public static function openTcpV4Connection(): self
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (false === $socket) {
            throw new RuntimeException('Unable to create socket');
        }

        return new self($socket);
    }

    public function __construct(
        private PhpSocket $resource,
    ) {
    }

    public function connect(string $host, int $port): void
    {
        if (false === socket_connect($this->resource, $host, $port)) {
            throw RuntimeException::fromSocketException($this->resource);
        }
    }

    public function write(string $content): int
    {
        $size = socket_write($this->resource, $content);
        if (false === $size) {
            throw RuntimeException::fromSocketException($this->resource);
        }

        return $size;
    }

    public function selectRead($sec = 0): bool
    {
        $usec = $sec === null ? null : (int) (($sec - floor($sec)) * 1000000);
        $r = array($this->resource);

        $size = socket_select($r, $x, $x, $sec, $usec);
        if (false === $size) {
            throw new RuntimeException('Failed to select socket for reading');
        }

        return !!$size;
    }

    public function read(int $length, int $type = PHP_BINARY_READ): string
    {
        $data = socket_read($this->resource, $length, $type);
        if (false === $data) {
            throw RuntimeException::fromSocketException($this->resource);
        }

        return $data;
    }
}
