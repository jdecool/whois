<?php

declare(strict_types=1);

namespace JDecool\Whois;

use JDecool\Whois\Exception\InvalidDomain;
use Spatie\Dns\Dns;

final class DnsClient
{
    public function __construct(
        private Dns $dns
    ) {
    }

    public function resolve(string $domain): string
    {
        if (false === filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidDomain($domain);
        }

        $records = $this->dns->getRecords($domain);

        return trim(
            array_reduce($records, static fn (string $output, string $record): string => $output.$record.PHP_EOL, ''),
        );
    }
}
