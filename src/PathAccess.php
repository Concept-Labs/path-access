<?php
namespace Concept\PathAccess;

use ArrayIterator;
use Traversable;

class PathAccess implements PathAccessInterface
{
     /**
     * Storage
     *
     * @var array
     */
    protected array $data = [];

    /**
     * States backup stack.
     * @todo: save to file/db etc.
     *
     * @var array<array>
     */
    protected array $state = [];

    /**
     * {@inheritDoc}
     */
    public function reset(): void
    {
        $this->data = [];
        $this->state = [];
    }

    /**
     * The constructor
     *
     * @param array $config
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initialize the config
     * 
     * @return self
     */
    protected function init(): self
    {
        return $this;
    }

    /**
     * Get the iterator
     * 
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function setData(?array $data = null): self
    {
        $this->data = $data ?? [];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withData(?array $data = null): self
    {
        $config = clone $this;
        $config->reset();
        $config->setData($data ?? []);

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function fromPath(string ...$paths): self
    {
        return $this->from(...$paths);
    }

    /**
     * {@inheritDoc}
     */
    public function from(string ...$paths): self
    {
        return $this->withData(
            $this->get(...$paths)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function get(string ...$paths)
    {
        /**
         * Each parameter can be part of path so join them together
         */
        $path = $this->createPath(...$paths);

        return array_reduce(// Lookup by the path
            $this->splitPath($path), 
            function ($reference, $key) {
                if (!is_array($reference) || !key_exists($key, $reference)) {
                    return null;
                }
                return $reference[$key];
            },
            $this->data
        );
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $path, $value): self
    {
        $keys = $this->splitPath($path);
        $lastKey = array_pop($keys);
        $reference = &$this->data;
        foreach ($keys as $key) {
            if (!is_array($reference)) {
                $reference = [];
            }
            if (!key_exists($key, $reference)) {
                $reference[$key] = [];
            }
            $reference = &$reference[$key];
        }
        $reference[$lastKey] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function unset(string ...$paths): self
    {
        $path = $this->createPath(...$paths);

        $keys = $this->splitPath($path);
        $lastKey = array_pop($keys);
        $reference = &$this->data;
        foreach ($keys as $key) {
            if (!is_array($reference) || !key_exists($key, $reference)) {
                return $this;
            }
            $reference = &$reference[$key];
        }
        unset($reference[$lastKey]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string ...$paths): bool
    {
        return null !== $this->get(...$paths);
    }

    /**
     * {@inheritDoc}
     */
    public function asArray(): array
    {
        return $this->get('');
    }

    /**
     * {@inheritDoc}
     */
    public function asJson(): string
    {
        return json_encode($this->asArray(), JSON_PRETTY_PRINT);
    }

     /**
     * {@inheritDoc}
     */
    public function merge(array $data): self
    {
        $this->data = array_replace_recursive($this->data, $data);

        return $this;
    }

    /**
     * @deprecated
     * {@inheritDoc}
     */
    public function mergeFrom(array $values):void
    {
        $this->data = $this->merge($values);
    }

    /**
     * {@inheritDoc}
     */
    public function pushState(): self
    {
        array_push($this->state, $this->data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function popState(): self
    {
        if (null !== $state = array_pop($this->state)) {
            $this->data = $state;
        }

        return $this;
    }
    
    /**
     * Split the path string by a separator. Default is @see const PATH_DEFAULT_SEPARATOR
     * next example works but it is not recommended:
     * Separator will be ignored inside double quotes.
     * e.g. `"11.2".3.5."another.key"` equals to an array access like $array["11.2"]["3"]["5"]["another.key"]
     *
     * @param string $path the Path string
     * 
     * @return array
     */
    protected function splitPath(string $path): array
    {
        return
            array_filter( // Remove empty items
                array_map( // Trim double quotes
                    fn($item) => trim($item, '"'),
                    preg_split($this->getSplitRegexp(), $path)
                )
            );
    }

    /**
     * @param string ...$paths
     * 
     * @return string
     */
    public function createPath(string ...$paths): string
    {
        return implode(self::PATH_SEPARATOR, $paths);
    }

    /**
     * {@inheritDoc}
     */
    public function path(string ...$paths): string
    {
        return $this->createPath(...$paths);
    }

    /**
     * Get the regular expression pattern for splitting the path.
     *
     * @return string
     */
    protected function getSplitRegexp(): string
    {
        return sprintf(
            '/%s(?=(?:[^"]*"[^"]*")*(?![^"]*"))/',
            preg_quote(static::PATH_SEPARATOR)
        );
    }
}