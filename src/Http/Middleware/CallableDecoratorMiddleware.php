<?php

namespace Tolkam\Application\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tolkam\Application\Http\HttpApplicationException;

class CallableDecoratorMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    protected $middleware;

    /**
     * @param callable $middleware
     */
    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @inheritDoc
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = ($this->middleware)($request, $handler);

        if (!($response instanceof ResponseInterface)) {
            throw new HttpApplicationException(sprintf(
                'Callable must return an instance of %s, %s returned',
                ResponseInterface::class,
                gettype($response)
            ));
        }

        return $response;
    }
}
