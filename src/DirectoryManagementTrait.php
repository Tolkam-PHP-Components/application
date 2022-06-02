<?php declare(strict_types=1);

namespace Tolkam\Application;

use Tolkam\Application\Http\HttpApplicationException;

trait DirectoryManagementTrait
{
    /**
     * registered directories
     * @var array
     */
    private array $directories = [];

    /**
     * Directory separator
     * @var string
     */
    private static string $sep = '/';

    /**
     * @inheritDoc
     */
    public function registerDirectory(string $name, string $path, array $placeholders = []): ApplicationInterface
    {
        /**
         * Directory path may refer other directories
         * by their name prefixed with `@` - `@root/public`
         */
        $referencer = '@';

        /**
         * Directory path may contain placeholders, like "{some_dynamic_value}"
         * that will be replaced with values from $placeholders array
         */
        $phOpenTag = '{';
        $phCloseTag = '}';

        if (array_key_exists($name, $this->directories)) {
            throw new HttpApplicationException(sprintf('Directory "%s" is already registered', $name));
        }

        if (empty($path)) {
            throw new HttpApplicationException(sprintf('"%s" directory path must not be empty', $name));
        }

        // apply placeholders
        if (mb_strpos($path, $phOpenTag) !== false) {
            $path = preg_replace_callback('~' . $phOpenTag . '(.+?)' . $phCloseTag . '~', static function ($matches) use ($placeholders, $path) {
                return $placeholders[$matches[1]] ?? $path;
            }, $path);
        }

        // resolve references
        if (mb_strpos($path, $referencer) !== false) {
            $path = preg_replace_callback('~' . $referencer . '([^/]+)~i', function ($matches) {
                return $this->getDirectory($matches[1]);
            }, $path);
        }

        $this->directories[$name] = $this->normalize($path);

        /** @var ApplicationInterface $this */
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerDirectories(array $directories, array $placeholders = []): ApplicationInterface
    {
        foreach ($directories as $k => $v) {
            $this->registerDirectory($k, $v, $placeholders);
        }

        /** @var ApplicationInterface $this */
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDirectory(string $name, array $children = []): string
    {
        $separator = self::$sep;

        if (!array_key_exists($name, $this->directories)) {
            throw new HttpApplicationException(sprintf('Directory "%s" is not registered', $name));
        }

        $childPath = !empty($children)
            ? $this->normalize(implode($separator, $children))
            : '';

        return $this->directories[$name] . $childPath;
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

    /**
     * Normalizes path
     *
     * @param string $path
     *
     * @return string
     */
    protected function normalize(string $path): string
    {
        $separator = self::$sep;
        $path = rtrim($path, $separator) . $separator;

        return preg_replace('~' . $separator . '{2,}~', $separator, $path);
    }
}
