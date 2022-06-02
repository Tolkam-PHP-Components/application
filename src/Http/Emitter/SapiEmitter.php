<?php declare(strict_types=1);

namespace Tolkam\Application\Http\Emitter;

use Psr\Http\Message\ResponseInterface;

class SapiEmitter implements ResponseEmitterInterface
{
    /**
     * @inheritDoc
     */
    public function emit(ResponseInterface $response): bool
    {
        $this->ensureEmitPossible();

        $this->emitStatus($response);
        $this->emitHeaders($response);
        $this->emitBody($response);

        return true;
    }

    /**
     * Ensures emitting is possible and to data is sent already
     *
     * @throws EmitterException
     */
    protected function ensureEmitPossible()
    {
        if (headers_sent()) {
            throw new EmitterException('Unable to emit response, headers already sent');
        }

        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw new EmitterException('Unable to emit response, body already sent');
        }
    }

    /**
     * Emits the status line
     *
     * @param ResponseInterface $response
     */
    protected function emitStatus(ResponseInterface $response)
    {
        $protocolVersion = $response->getProtocolVersion();
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();

        header(trim(sprintf('HTTP/%s %d %s', $protocolVersion, $statusCode, $reasonPhrase)), true, $statusCode);
    }

    /**
     * Emits the response headers
     *
     * @param ResponseInterface $response
     */
    protected function emitHeaders(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            $this->emitHeaderLine($name, $values);
        }
    }

    /**
     * Emits header line
     *
     * @param string $name
     * @param mixed  $values
     */
    protected function emitHeaderLine(string $name, $values)
    {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $this->normalizeHeaderName($name), $value), false);
        }
    }

    /**
     * Emits the response body
     *
     * @param ResponseInterface $response
     */
    protected function emitBody(ResponseInterface $response)
    {
        echo $response->getBody();
    }

    /**
     * Normalizes header name
     *
     * @param string $name
     *
     * @return array|string|string[]
     */
    protected function normalizeHeaderName(string $name)
    {
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);

        return str_replace(' ', '-', $name);
    }
}
