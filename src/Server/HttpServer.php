<?php

declare(strict_types=1);

namespace JDecool\Whois\Server;

use FastRoute\{
    Dispatcher,
    RouteCollector,
};
use JDecool\Whois\{
    DnsClient,
    WhoisClient,
};
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
use Throwable;
use function FastRoute\simpleDispatcher;

class HttpServer
{
    private LoopInterface $loop;
    private Socket $socket;
    private WhoisClient $client;
    private DnsClient $dns;

    public function __construct(WhoisClient $client, DnsClient $dns, string $ip = '0.0.0.0', int $port = 8000)
    {
        $this->client = $client;
        $this->dns = $dns;
        $this->loop = Factory::create();
        $this->socket = new Socket(sprintf('%s:%d', $ip, $port), $this->loop);

        $this->dispatcher = simpleDispatcher(function(RouteCollector $routes) {
            $routes->addRoute('GET', '/', static function(ServerRequestInterface $request): ResponseInterface {
                return new Response(200, ['Content-Type' => 'text/plain'], "Service is running");
            });

            $routes->addRoute('POST', '/dns', function(ServerRequestInterface $request): ResponseInterface {
                try {
                    $data = json_decode($request->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return new Response(400, ['Content-Type' => 'text/plain'], "Invalid request ({$e->getMessage()})");
                } catch (Throwable $e) {
                    return new Response(500, ['Content-Type' => 'text/plain'], "Internal Server Error ({$e->getMessage()})");
                }

                if (!isset($data['domain'])) {
                    return new Response(400, ['Content-Type' => 'text/plain'], "Missing domain key");
                }

                $domain = parse_url($data['domain'], PHP_URL_HOST);
                if (!is_string($domain)) {
                    return new Response(400, ['Content-Type' => 'text/plain'], "Invalid domain");
                }

                return new Response(200, ['Content-Type' => 'text/plain'], $this->dns->resolve($domain));
            });

            $routes->addRoute('GET', '/ip', static function(ServerRequestInterface $request): ResponseInterface {
                return new Response(200, ['Content-Type' => 'text/plain'], $request->getServerParams()['REMOTE_ADDR'] ?? 'Unknow');
            });

            $routes->addRoute('POST', '/whois', function(ServerRequestInterface $request): ResponseInterface {
                try {
                    $data = json_decode($request->getBody()->getContents(), true, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return new Response(400, ['Content-Type' => 'text/plain'], "Invalid request ({$e->getMessage()})");
                } catch (Throwable $e) {
                    return new Response(500, ['Content-Type' => 'text/plain'], "Internal Server Error ({$e->getMessage()})");
                }

                if (!isset($data['domain'])) {
                    return new Response(400, ['Content-Type' => 'text/plain'], "Missing domain key");
                }

                $domain = parse_url($data['domain'], PHP_URL_HOST);
                if (!is_string($domain)) {
                    return new Response(400, ['Content-Type' => 'text/plain'], "Invalid domain");
                }

                try {
                    return new Response(200, ['Content-Type' => 'text/plain'], $this->client->whois($domain));
                } catch (Throwable $e) {
                    return new Response(500, ['Content-Type' => 'text/plain'], "Internal Server Error ({$e->getMessage()})");
                }
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
