<?php

declare(strict_types=1);

namespace JDecool\Whois\Server;

use JDecool\Whois\{
    DnsClient,
    Exception\RuntimeException,
    SocketFactory,
    WhoisClient,
};
use React\EventLoop\Loop;
use React\Socket\Server;
use Sikei\React\Http\Middleware\CorsMiddleware;
use Spatie\Dns\Dns;

function bootstrap(
    array $serverConfiguration,
    string $configurationFile,
    string $templateDirectory,
    string $ip,
    int $port,
): HttpServer {
    $loop = Loop::get();
    $server = new Server(sprintf('%s:%d', $ip, $port), $loop);

    $templateDirectory = rtrim($templateDirectory, '/');

    return new HttpServer(
        $server,
        $loop,
        new CorsMiddleware($serverConfiguration['cors'] ?? []),
        WhoisClient::fromConfiguration($configurationFile, new SocketFactory()),
        new DnsClient(new Dns()),
        static function (string $file) use ($templateDirectory): string {
            $path = sprintf('%s/%s', $templateDirectory, $file);
            if (!file_exists($path)) {
                throw new RuntimeException("Template '$file' not exists in '$templateDirectory'.");
            }

            $content = file_get_contents($path);
            if (!is_string($content)) {
                throw new RuntimeException("Unable to get content of '$path'.");
            }

            return $content;
        },
    );
}
