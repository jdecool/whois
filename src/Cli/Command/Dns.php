<?php

declare(strict_types=1);

namespace JDecool\Whois\Cli\Command;

use JDecool\Whois\DnsClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Dns extends Command
{
    private DnsClient $client;

    public function __construct(DnsClient $client)
    {
        parent::__construct('dns');

        $this->client = $client;
    }

    protected function configure()
    {
        $this->addArgument('domain', InputArgument::REQUIRED, 'Domain name to looking for');
        $this->setDescription('The Domain Name System (DNS) is a hierarchical and decentralized naming system for computers, services, or other resources connected to the Internet or a private network.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            $this->client->resolve($input->getArgument('domain')),
        );

        return 0;
    }
}
