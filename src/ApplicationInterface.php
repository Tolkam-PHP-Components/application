<?php declare(strict_types=1);

namespace Tolkam\Application;

interface ApplicationInterface extends DirectoryManagementAwareInterface
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
}
