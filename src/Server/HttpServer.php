<?php

declare(strict_types=1);

namespace JDecool\Whois\Server;

use JDecool\Whois\WhoisClient;
use JsonException;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
};
use React\EventLoop\{
    Factory,
    LoopInterface,
};
use React\Http\{
    Response,
    Server,
};
use React\Socket\Server as Socket;

class HttpServer
{
    private LoopInterface $loop;
    private Socket $socket;
    private WhoisClient $client;

    public function __construct(WhoisClient $client, string $ip = '0.0.0.0', int $port = 8000)
    {
        $this->client = $client;
        $this->loop = Factory::create();
        $this->socket = new Socket(sprintf('%s:%d', $ip, $port), $this->loop);
    }

    public function run(): void
    {
        $server = new Server([$this, 'handleRequest']);
        $server->listen($this->socket);

        $this->loop->run();
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        if ('/whois' !== $request->getUri()->getPath()) {
            return new Response(404);
        }

        if ('POST' !== $request->getMethod()) {
            return new Response(400, ['Content-Type' => 'text/plain'], 'Invalid HTTP method');
        }

        try {
            $data = json_decode($request->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return new Response(400, ['Content-Type' => 'text/plain'], "Invalid request ({$e->getMessage()})");
        }

        return new Response(200, ['Content-Type' => 'text/plain'], $this->client->whois($data['domain']));
    }
}
