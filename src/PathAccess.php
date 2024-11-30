<?php
/**
 * PathAccess
 *
 * @package     Concept\PathAccess
 * @subpackage  Data container
 * @author      Victor Galitsky (mtr) concept.galitsky@gmail.com
 * @license     https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 * @link        https://github.com/concept-labs/di 
 */
namespace Concept\PathAccess;

use ArrayIterator;
use Concept\PathAccess\Exception\ReferenceException;
use Traversable;

class PathAccess implements PathAccessInterface
{

    private string $pathsSeparator = '.';

     /**
     * Storage
     *
     * @var array
     */
    protected array $data = [];

    private array $createdFromPath = [];

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
     * 
     */
    public function __construct(/*ProviderFactoryInterface $providerFactory*/)
    {
        //$this->providerFactory = $providerFactory;

        $this->init();
    }

    public function __clone()
    {
        $this->state = [];
    }

    // public function withDataProvider(DataProviderInterface $provider): self
    // {
    //     $clone = clone $this;
    //     $clone->provider = $provider;

    //     return $clone;
    // }
        

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
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPathSeparator(string $separator): self
    {
        $this->pathsSeparator = $separator;

        return $this;
    }

    /**
     * Get the path separator
     */
    protected function getPathSeparator(): string
    {
        return $this->pathsSeparator;
    }

    /**
     * {@inheritDoc}
     */
    public function withData(array $data): self
    {
        $clone = clone $this;
        $clone->reset();
        $clone->setData($data);

        return $clone;
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
        $data = $this->get(...$paths);
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Data must be an array. Given Path: ' . $this->createPath(...$paths));
        }
        $fromConfig = $this->withData(
            $this->get(...$paths)
        );

        $fromConfig->createdFromPath = $paths;

        return $fromConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedFromPath(): array
    {
        return $this->createdFromPath;
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

                if (is_string($reference[$key]) && strpos($reference[$key], '@') === 0) {
                    //@debug
                    $value = $this->resolveReference($reference[$key]);
                    if (empty($value)) {
                        throw new ReferenceException(sprintf('Reference "%s" not found', $reference[$key]));
                    }
                    $reference[$key] = $value;
                    //
                }

                return $reference[$key];
            },
            $this->data
        );
    }

    /**
     * {@inheritDoc}
     */
    public function has(string ...$paths): bool
    {
        return null !== $this->_has(...$paths);
    }

    /**
     * Keeping the original method for check if the value of node exists without resolving references
     * If logic of get() method will be changed, this method should be updated too
     */
    protected function _has(string ...$paths)
    {
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
    public function asArray(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function asJson(int $flags = 0): string
    {
        return json_encode($this->asArray(), $flags);
    }

     /**
     * {@inheritDoc}
     */
    public function merge($data): self
    {
        if (!is_array($data) && !($data instanceof PathAccessInterface)) {
            throw new \InvalidArgumentException('Data must be an array or an instance of ' . self::class);
        }

        if ($data instanceof PathAccessInterface) {
            $data = $data->asArray();
        }

        $this->data = array_replace_recursive($this->data, $data);
        

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function mergeTo(string $path, array $data): self
    {
        $this->set($path, array_replace_recursive($this->get($path) ?? [], $data));

        return $this;
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
     * {@inheritDoc}
     */
    public function resetState(): self
    {
        while (null !== $state = array_pop($this->state)) {
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
        return implode($this->getPathSeparator(), $paths);
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
            preg_quote($this->getPathSeparator())
        );
    }


    /**
     * Resolve a reference
     * 
     * @param array $reference
     * @param string $value
     * 
     * @return mixed
     */
    protected function resolveReference(string $value)
    {
        $protocol = substr($value, 1, strpos($value, '://') - 1);
        $providerString = substr($value, strpos($value, '://') + 3);

        if ($protocol === 'path') {
            return $this->get($providerString);
        }

        throw new \RuntimeException(sprintf('Unsupported reference protocol: %s', $protocol));

        // $provider = preg_replace('#^@([0-9a-zA-Z-_)+:#', '', $value);
        // $providerString = preg_replace('#^@([0-9a-zA-Z-_)+:#', '', $value);
        // $data = $this->getProvider($provider)->load($providerString);

        // return $data;
    }

    // protected function getProvider(string $providerId): DataProviderInterface
    // {
    //     return $this->getProviderFactory()->create($providerId);
    // }

    // /**
    //  * Get the provider factory
    //  * 
    //  * @return ProviderFactoryInterface
    //  */
    // protected function getProviderFactory(): ProviderFactoryInterface
    // {
    //     return $this->providerFactory;
    // }
}