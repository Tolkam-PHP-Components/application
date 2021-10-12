# tolkam/application

HTTP(PSR-15)/CLI application.

## Documentation

The code is rather self-explanatory and API is intended to be as simple as possible. Please, read the sources/Docblock if you have any questions. See [Usage](#usage) for quick start.

## Usage

````php
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tolkam\Application\Http\Emitter\SapiEmitter;
use Tolkam\Application\Http\HttpApplication;
use Tolkam\Application\Http\Middleware\ErrorHandlerMiddleware;

$app = new HttpApplication();
$responseFactory = new ResponseFactory();

$myMiddleware = new class implements MiddlewareInterface {
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $response = (new ResponseFactory())->createResponse();
        $response->getBody()->write('Hello from HttpApplication!');
        
        return $response;
    }
};

$app
    // add response emitter
    ->addEmitter(new SapiEmitter())
    
    // add desired middlewares
    ->addMiddleware(new ErrorHandlerMiddleware($responseFactory))
    ->addMiddleware($myMiddleware)
    
    // run the app
    ->run(ServerRequestFactory::fromGlobals());
````

## License

Proprietary / Unlicensed 🤷
