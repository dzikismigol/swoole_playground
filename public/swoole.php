<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use \Symfony\Component\HttpFoundation\Request as SymfonyRequest;

$server = new HttpServer("0.0.0.0", 9501);

$kernel = new \App\Kernel("dev", false);;

$server->on("start", function (HttpServer $server) use ($kernel) {
    echo "kernel booted" . PHP_EOL;

});

$server->on("request", function (Request $request, Response $response) use ($kernel) {
    var_dump($request);
    $kernel->reboot($kernel->getCacheDir());
    echo "Request came in" . PHP_EOL;
    $symfonyResponse = $kernel->handle(mapRequest($request));

    foreach ($symfonyResponse->headers->allPreserveCaseWithoutCookies() as $name => $value) {
        $response->header($name, $value);
        $response->status($symfonyResponse->getStatusCode());
        $response->end($symfonyResponse->getContent());
    }

});

$server->start();


function mapRequest(Request $request): SymfonyRequest
{
    return new SymfonyRequest(
        $request->get ?? [],
        $request->post ?? [],
        [],
        $request->cookie ?? [],
        $request->files ?? [],
        $request->server,
        $request->rawContent()
    );
}
