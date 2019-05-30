<?php

declare(strict_types=1);

namespace JDecool\Whois;

class SocketFactory
{
    public function open(string $host, int $port): Socket
    {
        $socket = Socket::openTcpV4Connection();
        $socket->connect($host, $port);

        return $socket;
    }
}
