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
use React\EventLoop\LoopInterface;
use React\Http\{
    Message\Response,
    Server,
};
use React\Socket\Server as Socket;
use Sikei\React\Http\Middleware\CorsMiddleware;
use Throwable;
use function FastRoute\simpleDispatcher;

class HttpServer
{
    private Dispatcher $dispatcher;

    public function __construct(
        private Socket $socket,
        private LoopInterface $loop,
        private CorsMiddleware $cors,
        private WhoisClient $client,
        private DnsClient $dns,
        callable $templateResolver,
    ) {
        $this->dispatcher = simpleDispatcher(function(RouteCollector $routes) use ($templateResolver) {
            $routes->addRoute('GET', '/', static function(ServerRequestInterface $request) use ($templateResolver): ResponseInterface {
                try {
                    return new Response(200, ['Content-Type' => 'text/html'], $templateResolver('index.html'));
                } catch (Throwable $e) {
                    return new Response(500, ['Content-Type' => 'text/plain'], $e->getMessage());
                }
            });

            $routes->addRoute('GET', '/favico.png', static function(ServerRequestInterface $request) use ($templateResolver): ResponseInterface {
                return new Response(200, ['Content-Type' => 'image/png'], $templateResolver('favico.png'));
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
        $server = new Server(
            $this->cors,
            function(ServerRequestInterface $request) {
                $route = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

                switch ($route[0]) {
                    case Dispatcher::NOT_FOUND:
                        return new Response(404, ['Content-Type' => 'text/plain'],  'Not found');
                    case Dispatcher::FOUND:
                        $params = $route[2];
                        return $route[1]($request, ... array_values($params));
                    case Dispatcher::METHOD_NOT_ALLOWED:
                        return new Response(405, ['Content-Type' => 'text/plain'], 'Method not allowed');
                }

                throw new LogicException('Something wrong with routing');
            },
        );
        $server->listen($this->socket);

        $this->loop->run();
    }
}
