<?php

declare(strict_types=1);

namespace JDecool\Whois\Cli\Command;

use JDecool\Whois\WhoisClient;
use Symfony\Component\Console\{
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
};

final class Whois extends Command
{
    public function __construct(
        private WhoisClient $client,
    ) {
        parent::__construct('whois');
    }

    protected function configure()
    {
        $this->addArgument('domain', InputArgument::REQUIRED, 'Domain name to looking for');
        $this->setDescription('The Whois database contains details such as the registration date of the domain name, when it expires, ownership and contact information, nameserver information of the domain, the registrar via which the domain was purchased, etc.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            $this->client->whois($input->getArgument('domain'))
        );

        return 0;
    }
}
