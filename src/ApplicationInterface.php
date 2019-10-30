<?php declare(strict_types=1);

namespace Tolkam\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tolkam\Application\Emitter\ResponseEmitterInterface;
use Tolkam\EventManager\EventsAwareInterface;

interface ApplicationInterface extends RequestHandlerInterface, EventsAwareInterface
{
    /**
     * app environments
     */
    const ENV_DEVELOPMENT = 'development';
    const ENV_TESTING     = 'testing';
    const ENV_STAGING     = 'staging';
    const ENV_PRODUCTION  = 'production';

    /**
     * known environments
     */
    const KNOWN_ENVIRONMENTS = [
        self::ENV_DEVELOPMENT,
        self::ENV_TESTING,
        self::ENV_STAGING,
        self::ENV_PRODUCTION,
    ];

    /**
     * Gets the current environment value
     *
     * @return string
     */
    public function getEnvironment(): string;
    
    /**
     * Sets default response
     *
     * @param ResponseInterface $defaultResponse
     */
    public function setDefaultResponse(ResponseInterface $defaultResponse): void;

    /**
     * Adds middleware
     *
     * @param  MiddlewareInterface $middleware
     * @return ApplicationInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): self;

    /**
     * Adds middlewares from array
     *
     * @param  array  $middlewares
     * @return ApplicationInterface
     */
    public function addMiddlewares(array $middlewares): self;
    
    /**
     * Adds emitter to the stack
     *
     * @param ResponseEmitterInterface $emitter
     *
     * @return ApplicationInterface
     */
    public function addEmitter(ResponseEmitterInterface $emitter): self;
    
    /**
     * Adds emitters from array
     *
     * @param ResponseEmitterInterface[] $emitters
     *
     * @return ApplicationInterface
     */
    public function addEmitters(array $emitters): self;
    
    /**
     * Runs the middlewares and emits the response
     *
     * @param ServerRequestInterface $request
     *
     * @throws ApplicationException
     */
    public function run(ServerRequestInterface $request);

    /**
     * Registers application directory
     *
     * Directory path may refer other directories
     * by their name prefixed with `@` - `@root/public`
     *
     *
     * @param  string $name
     * @param  string $path
     * @return self
     */
    public function registerDirectory(string $name, string $path): self;

    /**
     * Registers array of directories
     *
     * @param  array  $directories
     * @return self
     */
    public function registerDirectories(array $directories): self;
    
    /**
     * Gets directory path by name
     *
     * @param string $name
     * @param string[] $children
     *
     * @return string
     */
    public function getDirectory(string $name, array $children = []): string;

    /**
     * Gets registered directories array
     *
     * @return array
     */
    public function getDirectories(): array;

    /**
     * Creates named directories recursively
     *
     * @param  array  $names
     * @param  int    $mask
     * @param  bool   $recursive
     * @return void
     */
    public function createDirectories(array $names, int $mask = 0775, bool $recursive = true): void;
}
