<?php

declare(strict_types=1);

namespace JDecool\Whois\Cli;

use JDecool\Whois\{
    DnsClient,
    SocketFactory,
    WhoisClient,
};
use Spatie\Dns\Dns;

function bootstrap(string $configurationFile): Application
{
    return new Application(
        WhoisClient::fromConfiguration($configurationFile, new SocketFactory()),
        new DnsClient(new Dns()),
    );
}
