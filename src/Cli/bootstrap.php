<?php

declare(strict_types=1);

namespace JDecool\Whois\Cli;

use JDecool\Whois\{
    SocketFactory,
    WhoisClient,
};

function bootstrap(string $configurationFile): Application
{
    return new Application(
        WhoisClient::fromConfiguration($configurationFile, new SocketFactory()),
    );
}
