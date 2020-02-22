<?php declare(strict_types=1);

namespace Tolkam\Application\Http\Emitter;

use Psr\Http\Message\ResponseInterface;

interface ResponseEmitterInterface
{
    /**
     * Emits the response to the client
     *
     * Emitter should return `false` if it was unable to emit the response
     * and the app will pass the response to the next emitter in stack to process
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function emit(ResponseInterface $response): bool;
}
