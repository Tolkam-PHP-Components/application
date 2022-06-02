<?php declare(strict_types=1);

namespace Tolkam\Application;

interface ApplicationInterface extends DirectoryManagementAwareInterface
{
    /**
     * app environments
     */
    public const ENV_DEVELOPMENT = 'development';
    public const ENV_TESTING     = 'testing';
    public const ENV_STAGING     = 'staging';
    public const ENV_PRODUCTION  = 'production';

    /**
     * known environments
     */
    public const KNOWN_ENVIRONMENTS = [
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
