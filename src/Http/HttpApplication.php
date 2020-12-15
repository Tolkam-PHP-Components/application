<?php declare(strict_types=1);

namespace Tolkam\Application\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tolkam\Application\ApplicationInterface;
use Tolkam\Application\DirectoryManagementTrait;
use Tolkam\Application\EnvironmentAwareTrait;
use Tolkam\Application\Http\Emitter\ResponseEmitterInterface;
use Tolkam\PSR15\Dispatcher\Dispatcher;

class HttpApplication implements ApplicationInterface, RequestHandlerInterface
{
    use EnvironmentAwareTrait;
    use DirectoryManagementTrait;
    
    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;
    
    /**
     * @var ResponseEmitterInterface[]
     */
    private array $emitters = [];
    
    /**
     * @var ResponseInterface|null
     */
    private ?ResponseInterface $defaultResponse = null;
    
    /**
     * @return void
     */
    public function __construct()
    {
        $this->dispatcher = Dispatcher::create($this);
    }
    
    /**
     * Sets default response
     *
     * @param ResponseInterface $defaultResponse
     *
     * @return self
     */
    public function setDefaultResponse(ResponseInterface $defaultResponse): self
    {
        $this->defaultResponse = $defaultResponse;
        
        return $this;
    }
    
    /**
     * Adds middleware
     *
     * @param MiddlewareInterface $middleware
     *
     * @return self
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->dispatcher->middleware($middleware);
        
        return $this;
    }
    
    /**
     * Adds middlewares from array
     *
     * @param array $middlewares
     *
     * @return self
     */
    public function addMiddlewares(array $middlewares): self
    {
        $this->dispatcher->middlewares($middlewares);
        
        return $this;
    }
    
    /**
     * Adds emitter to the stack
     *
     * @param ResponseEmitterInterface $emitter
     *
     * @return self
     */
    public function addEmitter(ResponseEmitterInterface $emitter): self
    {
        $this->emitters[] = $emitter;
        
        return $this;
    }
    
    /**
     * Adds emitters from array
     *
     * @param ResponseEmitterInterface[] $emitters
     *
     * @return self
     */
    public function addEmitters(array $emitters): self
    {
        foreach ($emitters as $emitter) {
            $this->addEmitter($emitter);
        }
        
        return $this;
    }
    
    /**
     * Runs the middlewares and emits the response
     *
     * @param ServerRequestInterface $request
     *
     * @throws HttpApplicationException
     */
    public function run(ServerRequestInterface $request): void
    {
        $this->emit($this->handle($request));
    }
    
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // when queue processing finished
        if ($this->dispatcher->isEmpty()) {
            if ($this->defaultResponse) {
                return $this->defaultResponse;
            }
            
            throw new HttpApplicationException(
                'Middlewares queue is empty or exhausted without response and no default response is set'
            );
        }
        
        return $this->dispatcher->handle($request);
    }
    
    /**
     * Loops through emitters stack passing the response to emit
     *
     * @param ResponseInterface $response
     *
     * @return bool
     * @throws HttpApplicationException
     */
    private function emit(ResponseInterface $response): bool
    {
        while ($emitter = array_pop($this->emitters)) {
            if ($emitted = $emitter->emit($response)) {
                return $emitted;
            }
        }
        
        throw new HttpApplicationException(
            'Emitters stack is empty or none of the emitters was able to emit the response'
        );
    }
}
