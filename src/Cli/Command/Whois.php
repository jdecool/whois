<?php

declare(strict_types=1);

namespace JDecool\Whois\Cli\Command;

use JDecool\Whois\WhoisClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Whois extends Command
{
    private WhoisClient $client;

    public function __construct(WhoisClient $client)
    {
        parent::__construct('whois');

        $this->client = $client;
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
