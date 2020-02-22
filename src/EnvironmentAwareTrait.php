<?php

namespace Tolkam\Application;

use Tolkam\Application\Http\HttpApplicationException;

trait EnvironmentAwareTrait
{
    /**
     * current environment
     * @var string
     */
    private $environment = ApplicationInterface::ENV_PRODUCTION;
    
    /**
     * @param string $environment
     *
     * @throws HttpApplicationException
     */
    public function setEnvironment(string $environment)
    {
        $knownEnvironments = ApplicationInterface::KNOWN_ENVIRONMENTS;
        if (!in_array($environment, $knownEnvironments)) {
            throw new HttpApplicationException(sprintf(
                'Unknown environment value, known values are "%s"',
                implode('", "', $knownEnvironments)
            ));
        }
        
        $this->environment = $environment;
    }
    
    /**
     * @inheritDoc
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
}
