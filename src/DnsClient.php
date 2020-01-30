<?php

declare(strict_types=1);

namespace JDecool\Whois;

use JDecool\Whois\Exception\InvalidDomain;
use Spatie\Dns\Dns;

final class DnsClient
{
    public function resolve(string $domain): string
    {
        if (false === filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidDomain($domain);
        }

        return (new Dns($domain))->getRecords();
    }
}
