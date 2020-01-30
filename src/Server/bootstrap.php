<?php

declare(strict_types=1);

namespace JDecool\Whois\Server;

use JDecool\Whois\{
    DnsClient,
    SocketFactory,
    WhoisClient};

function bootstrap(string $configurationFile, string $ip, int $port): HttpServer
{
    return new HttpServer(
        WhoisClient::fromConfiguration($configurationFile, new SocketFactory()),
        new DnsClient(),
        $ip,
        $port,
    );
}
