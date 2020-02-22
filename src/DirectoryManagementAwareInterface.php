<?php declare(strict_types=1);

namespace Tolkam\Application;

interface DirectoryManagementAwareInterface
{
    /**
     * Registers directory
     *
     * @param string $name
     * @param string $path
     *
     * @return self
     */
    public function registerDirectory(string $name, string $path): self;
    
    /**
     * Registers array of directories
     *
     * @param array $directories
     *
     * @return self
     */
    public function registerDirectories(array $directories): self;
    
    /**
     * Gets directory path by name
     *
     * @param string   $name
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
     * @param array $names
     * @param int   $mask
     * @param bool  $recursive
     *
     * @return void
     */
    public function createDirectories(array $names, int $mask = 0775, bool $recursive = true): void;
}
