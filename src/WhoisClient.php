<?php

declare(strict_types=1);

namespace JDecool\Whois;

use JDecool\Whois\Exception\{
    InvalidDomain,
    RuntimeException,
};
use function Safe\{
    file_get_contents,
    json_decode,
};

final class WhoisClient
{
    private const TIMEOUT = 30; // in seconds

    /**
     * @throws \Safe\Exceptions\FilesystemException
     * @throws \Safe\Exceptions\JsonException
     */
    public static function fromConfiguration(string $file, ?SocketFactory $socketFactory = null): self
    {
        $content = file_get_contents($file);

        return new self(
            $socketFactory ?? new SocketFactory(),
            json_decode($content, true),
        );
    }

    public function __construct(
        private SocketFactory $socketFactory,
        private array $servers,
    ) {
    }

    public function whois(string $domain): string
    {
        if (false === filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidDomain($domain);
        }

        $nicServer = $this->findDomainNicServer($domain);

        $socket = $this->socketFactory->open($nicServer, 43);
        $socket->write("$domain\r\n");

        $response = '';
        while ($socket->selectRead(self::TIMEOUT)) {
            $recv = $socket->read(8192);
            if ('' === $recv) {
                return trim($response);
            }

            $response .= $recv;
        }

        return $response;
    }

    private function findDomainNicServer(string $domain): string
    {
        foreach ($this->servers as $regex => $host) {
            if (@preg_match("/$regex/", $domain)) {
                return $host;
            }
        }

        throw new RuntimeException();
    }
}
