<?php
namespace Concept\PathAccess;

use IteratorAggregate;

interface PathAccessInterface extends IteratorAggregate
{
    /**
     * The path separatog. e.g. "key.subkey.subsubkey"
     */
    const PATH_SEPARATOR = '.';

    /**
     * Check if the data has value by path
     *
     * @param string ...$paths List of paths e.g has("key", "subkey", "subkey")
     *                         Or a path e.g. has("key.subkey.subsubkey")
     *                         First way is preferred because 
     *                         it uses defined separator automatically
     * 
     * @return bool
     */
    function has(string ...$paths): bool;

    /**
     * Get the value by path
     * 
     * @return mixed
     */
    function get(string ...$paths);

    /**
     * @param string $paths
     * @param mixed $value
     * 
     * @return self
     */
    function set(string $path, $value): self;

    /**
     * Unset the value by path
     * 
     * @param string ...$paths
     * 
     * @return self
     */
    public function unset(string ...$paths): self;

    /**
     * Set the data
     *
     * @param array|null $data The data
     * 
     * @return self
     */
    public function setData(?array $data = null): self;


    /**
     * Get self instance with new data
     *
     * @param array|null $data The data @see setData()
     * 
     * @return self
     */
    public function withData(?array $data = null): self;

    /**
     * Merge into the data from a values
     *
     * @param array $values The array for merge from
     * 
     * @return void
     */
    public function merge(array $data): self;
    
    /**
     * Get cloned instance with data taken by path
     *
     * @param string ...$paths The paths. @see get()
     * 
     * @return self
     */
    //public function fromPath(string ...$paths);
    public function from(string ...$paths): self;
    /**
     * @deprecated
     * Get the path to the  node
     * 
     * @param string ...$paths
     * 
     * @return self
     */
    public function fromPath(string ...$paths): self;

    

    /**
     * Get the path to the  node
     * 
     * @param string ...$paths
     * 
     * @return string
     */
    public function path(string ...$paths): string;

    /**
     * Saves the current state  into stack
     *
     * @return void
     */
    public function pushState(): self;
    
    /**
     * Restore previous state 
     *
     * @return void
     */
    public function popState(): self;

    /**
     * Get the all data
     * 
     * @return array
     */
    public function asArray(): array;

    /**
     * Get the all data as JSON
     * 
     * @return string
     */
    public function asJson(): string;

    /**
     * Reset the state of self instance
     *
     * @return void
     */
    public function reset(): void;
}