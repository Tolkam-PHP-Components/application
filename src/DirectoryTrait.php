<?php declare(strict_types=1);

namespace Tolkam\Application;

trait DirectoryTrait
{
    /**
     * registered directories
     * @var array
     */
    private $directories = [];
    
    /**
     * @inheritDoc
     */
    public function registerDirectory(string $name, string $path): ApplicationInterface
    {
        $separator = DIRECTORY_SEPARATOR;
        $referencer = '@';
        
        if (array_key_exists($name, $this->directories)) {
            throw new ApplicationException(sprintf('Directory "%s" is already registered', $name));
        }
        
        if (empty($path)) {
            throw new ApplicationException(sprintf('"%s" directory path must not be empty', $name));
        }
        
        if (mb_strlen($path) > 1) {
            $path = rtrim($path, $separator);
        }
        
        // resolve references
        if (mb_strpos($path, $referencer) !== false) {
            $path = preg_replace_callback('~' . $referencer . '([^' . $separator . ']+)~i', function ($matches) {
                return $this->getDirectory($matches[1]);
            }, $path);
        }
        
        $this->directories[$name] = $path;
    
        /** @var ApplicationInterface $this */
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function registerDirectories(array $directories): ApplicationInterface
    {
        foreach ($directories as $k => $v) {
            $this->registerDirectory($k, $v);
        }
    
        /** @var ApplicationInterface $this */
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getDirectory(string $name): string
    {
        if (!array_key_exists($name, $this->directories)) {
            throw new ApplicationException(sprintf('Directory "%s" is not registered', $name));
        }
        
        return $this->directories[$name];
    }
    
    /**
     * @inheritDoc
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }
    
    /**
     * @inheritDoc
     */
    public function createDirectories(array $names, int $mask = 0775, bool $recursive = true): void
    {
        $directories = $this->directories;
        
        foreach ($names as $name) {
            if (!array_key_exists($name, $directories)) {
                continue;
            }
            
            $path = $directories[$name];
            if (!is_dir($path)) {
                mkdir($path, $mask, $recursive);
            }
        }
    }
}
