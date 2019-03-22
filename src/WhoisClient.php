<?php

declare(strict_types=1);

namespace JDecool\Whois;

use JDecool\Whois\Exception\InvalidDomain;

final class WhoisClient
{
    private const TIMEOUT = 30; // in seconds

    private Socket $socket;
    private string $host;
    private int $port;

    public static function create(string $host, int $port = 43): self
    {
        return new self(
            Socket::openTcpV4Connection(),
            $host,
            $port,
        );
    }

    public function __construct(Socket $socket, string $host, int $port = 43)
    {
        $this->socket = $socket;
        $this->host = $host;
        $this->port = $port;
    }

    public function whois(string $domain): string
    {
        if (false === filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidDomain($domain);
        }

        $this->socket->connect($this->host, $this->port);
        $this->socket->write("$domain\r\n");

        $response = '';
        while ($this->socket->selectRead(self::TIMEOUT)) {
            $recv = $this->socket->read(8192);
            if ('' === $recv) {
                return trim($response);
            }

            $response .= $recv;
        }

        return $response;
    }
}
