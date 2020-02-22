<?php declare(strict_types=1);

namespace Tolkam\Application\Cli;

use Symfony\Component\Console\Application as SymfonyConsole;
use Tolkam\Application\ApplicationInterface;
use Tolkam\Application\DirectoryManagementTrait;
use Tolkam\Application\EnvironmentAwareTrait;

class CliApplication extends SymfonyConsole implements ApplicationInterface
{
    use EnvironmentAwareTrait;
    use DirectoryManagementTrait;
}
