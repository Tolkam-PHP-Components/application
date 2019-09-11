<?php declare(strict_types=1);

namespace Tolkam\Application\Middleware\ErrorHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Tolkam\Application\HttpException;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;
    
    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $t) {
            if ($this->shouldBeLogged($t)) {
                error_log($t->__toString());
            }
            $response = $this->handleException($t);
        }

        return $response;
    }
    
    /**
     * Gets the response factory
     *
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory()
    {
        return $this->responseFactory;
    }
    
    /**
     * Checks if exception should be logged
     *
     * @param  Throwable $t
     *
     * @return bool
     */
    protected function shouldBeLogged(Throwable $t): bool
    {
        $shouldBeLogged = true;

        if ($t instanceof HttpException) {
            $code = $t->getCode();
            $shouldBeLogged = $code === 500 || ($code > 599 || $code < 200);
        }

        return $shouldBeLogged;
    }
    
    /**
     * Gets http status code from thrown exception
     *
     * @param  Throwable $t
     *
     * @return int
     */
    protected function getStatusCode(Throwable $t): int
    {
        return $t instanceof HttpException ? $t->getCode() : 500;
    }
    
    /**
     * Gets response headers
     *
     * @param  Throwable $t
     *
     * @return array
     */
    protected function getHeaders(Throwable $t): array
    {
        return [];
    }
    
    /**
     * Gets response body
     *
     * @param  Throwable $t
     *
     * @return string
     */
    protected function getBody(Throwable $t): string
    {
        $statusCode = $this->getStatusCode($t);
        $reasonPhrase = $this->getResponseFactory()->createResponse($statusCode)->getReasonPhrase();

        if ($t instanceof HttpException) {
            $reasonPhrase = $t->getMessage();
        }

        return 'Error ' . $statusCode . ': ' . $reasonPhrase;
    }
    
    /**
     * Handles exception
     *
     * @param  Throwable $t
     *
     * @return ResponseInterface
     */
    private function handleException(Throwable $t): ResponseInterface
    {
        $statusCode = $this->getStatusCode($t);
        $headers = $this->getHeaders($t);
        $body = $this->getBody($t);

        $response = $this->getResponseFactory()->createResponse($statusCode);
        foreach ($headers as $k => $v) {
            $response = $response->withHeader($k, $v);
        }

        $response->getBody()->write($body);

        return $response;
    }
}
