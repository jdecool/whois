<?php

declare(strict_types=1);

namespace JDecool\Whois\Cli;

use JDecool\Whois\Cli\Command;
use JDecool\Whois\DnsClient;
use JDecool\Whois\WhoisClient;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const NAME = 'whois';
    public const VERSION = 'alpha';

    public function __construct(WhoisClient $whoisClient, DnsClient $dnsClient)
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->addCommands([
            new Command\Whois($whoisClient),
            new Command\Dns($dnsClient),
        ]);
    }
}
