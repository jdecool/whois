<?php

declare(strict_types=1);

namespace JDecool\Whois\Server;

use JDecool\Whois\WhoisClient;

function bootstrap(string $ip, int $port): HttpServer
{
    return new HttpServer(WhoisClient::create('whois.nic.fr'), $ip, $port);
}
