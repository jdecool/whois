<?php

declare(strict_types=1);

namespace JDecool\Whois\Server;

use FastRoute\{
    Dispatcher,
    RouteCollector,
};
use JDecool\Whois\WhoisClient;
use JsonException;
use LogicException;
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
use function FastRoute\simpleDispatcher;

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

        $this->dispatcher = simpleDispatcher(function(RouteCollector $routes) {
            $routes->addRoute('POST', '/whois', function(ServerRequestInterface $request): ResponseInterface {
                try {
                    $data = json_decode($request->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return new Response(400, ['Content-Type' => 'text/plain'], "Invalid request ({$e->getMessage()})");
                }

                return new Response(200, ['Content-Type' => 'text/plain'], $this->client->whois($data['domain']));
            });
        });
    }

    public function run(): void
    {
        $server = new Server(function(ServerRequestInterface $request) {
            $route = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

            switch ($route[0]) {
                case Dispatcher::NOT_FOUND:
                    return new Response(404, ['Content-Type' => 'text/plain'],  'Not found');
                case Dispatcher::FOUND:
                    $params = $route[2];
                    return $route[1]($request, ... array_values($params));
            }

            throw new LogicException('Something wrong with routing');
        });
        $server->listen($this->socket);

        $this->loop->run();
    }
}
