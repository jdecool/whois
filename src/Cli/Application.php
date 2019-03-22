<?php

declare(strict_types=1);

namespace JDecool\Whois\Cli;

use JDecool\Whois\Cli\Command;
use JDecool\Whois\WhoisClient;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    public const NAME = 'whois';
    public const VERSION = 'alpha';

    public function __construct(WhoisClient $client)
    {
        parent::__construct(self::NAME, self::VERSION);

        $this->addCommands([
            $default = new Command\Whois($client),
        ]);

        $this->setDefaultCommand($default->getName(), true);
    }
}
