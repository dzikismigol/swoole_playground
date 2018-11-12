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
    //    var_dump($request);
    //    $kernel->reboot();
    $kernel->boot();
    /** @var \Symfony\Component\Routing\RouterInterface $router */
    $router = $kernel->getContainer()->get('router');
    echo "Request came in" . PHP_EOL;
    var_dump($router->getRouteCollection()->count());
    //    var_dump($router->match(""));
    //    var_dump($router->match("/"));
    echo PHP_EOL;

    try {
        $symfonyRequest  = mapRequest($request);
        $symfonyResponse = $kernel->handle($symfonyRequest);

        foreach ($symfonyResponse->headers->allPreserveCaseWithoutCookies() as $name => $value) {
            $response->header($name, $value);
        }
        $response->status($symfonyResponse->getStatusCode());
        $response->end($symfonyResponse->getContent());
        $kernel->terminate($symfonyRequest, $symfonyResponse);
    } catch (\Throwable $t) {
        echo "Exception caught. " . $t->getMessage() . PHP_EOL . "Class: " . get_class($t) . PHP_EOL . PHP_EOL . $t->getTraceAsString() . PHP_EOL . PHP_EOL;
        echo PHP_EOL . $t->getPrevious()->getTraceAsString();
    }

});

$server->start();


function mapRequest(Request $request): SymfonyRequest
{

    SymfonyRequest::create()
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
