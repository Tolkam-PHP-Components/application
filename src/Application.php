<?php declare(strict_types=1);

namespace Tolkam\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Tolkam\Application\Emitter\ResponseEmitterInterface;
use Tolkam\Application\Event\AfterRunEvent;
use Tolkam\Application\Event\BeforeRunEvent;
use Tolkam\EventManager\EventsAwareTrait;
use Tolkam\PSR15\Dispatcher\Dispatcher;

class Application implements ApplicationInterface
{
    use DirectoryTrait;
    use EventsAwareTrait;
    
    /**
     * current environment
     * @var string
     */
    private $environment = self::ENV_PRODUCTION;
    
    /**
     * @var Dispatcher
     */
    private $dispatcher;
    
    /**
     * @var ResponseEmitterInterface[]
     */
    private $emitters = [];
    
    /**
     * @var ResponseInterface|null
     */
    private $defaultResponse;
    
    /**
     * @param string $environment
     *
     * @throws ApplicationException
     */
    public function __construct(string $environment = self::ENV_PRODUCTION)
    {
        $knownEnvironments = self::KNOWN_ENVIRONMENTS;
        if (!in_array($environment, $knownEnvironments)) {
            throw new ApplicationException(sprintf(
                'Unknown environment value, known values are "%s"',
                implode('", "', $knownEnvironments)
            ));
        }

        $this->environment = $environment;
        $this->dispatcher = Dispatcher::create($this);
    }

    /**
     * @inheritDoc
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
    
    /**
     * @inheritDoc
     */
    public function setDefaultResponse(ResponseInterface $defaultResponse): void
    {
        $this->defaultResponse = $defaultResponse;
    }

    /**
     * @inheritDoc
     */
    public function addMiddleware(MiddlewareInterface $middleware): ApplicationInterface
    {
        $this->dispatcher->middleware($middleware);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addMiddlewares(array $middlewares): ApplicationInterface
    {
        $this->dispatcher->middlewares($middlewares);
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function addEmitter(ResponseEmitterInterface $emitter): ApplicationInterface
    {
        $this->emitters[] = $emitter;
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function addEmitters(array $emitters): ApplicationInterface
    {
        foreach ($emitters as $emitter) {
            $this->addEmitter($emitter);
        }
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function run(ServerRequestInterface $request)
    {
        if ($eventManager = $this->eventManager) {
            $eventManager->emit(new BeforeRunEvent);
        }
        
        $this->emit($this->handle($request));
    
        if ($eventManager) {
            $eventManager->emit(new AfterRunEvent);
        }
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
            
            throw new ApplicationException(
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
     * @throws ApplicationException
     */
    private function emit(ResponseInterface $response): bool
    {
        while ($emitter = array_pop($this->emitters)) {
            if ($emitted = $emitter->emit($response)) {
                return $emitted;
            }
        }
    
        throw new ApplicationException(
            'Emitters stack is empty or none of the emitters was able to emit the response'
        );
    }
}
