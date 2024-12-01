<?php
/**
 * PathAccessInterface
 *
 * This interface defines the contract for accessing and manipulating paths within the application.
 *
 * @package     Concept\PathAccess
 * @subpackage  Data container
 * @author      Victor Galitsky (mtr) concept.galitsky@gmail.com
 * @license     https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 * @link        https://github.com/concept-labs/path-access 
 */
namespace Concept\PathAccess;

use IteratorAggregate;

interface PathAccessInterface extends IteratorAggregate
{

    /**
     * Set the path separator
     *
     * @param string $separator The separator
     * 
     * @return self
     */
    public function setPathSeparator(string $separator): self;

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
     * @see has()
     * 
     * @return mixed
     */
    function get(string ...$paths);

    /**
     * @param string $paths
     * @param mixed $value
     * @see has()
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
     * @param array $data The data
     * 
     * @return self
     */
    public function setData(array $data): self;


    /**
     * Get self instance with new data
     *
     * @param array $data The data @see setData()
     * 
     * @return self
     */
    public function withData(array $data): self;

    /**
     * Merge into the data from a values
     *
     * @param array|PathAccessInterface $values The array for merge from
     * 
     * @return self
     */
    public function merge($data): self;

    /**
     * Merge into the data from a values by path
     *
     * @param string $path The path
     * @param array $values The array for merge from
     * 
     * @return self
     */
    public function mergeTo(string $path, array $data): self;
    
    /**
     * Get cloned instance with data taken by path
     *
     * @param string ...$paths The paths. @see get()
     * 
     * @return self
     */
    //public function fromPath(string ...$paths);
    public function from(string ...$paths): ?self;

    /**
     * Get the path to the node from which the current config was created
     * Note: The path is relative to the root of the original config
     * 
     * @param string ...$paths
     * 
     * @return array
     */
    public function getCreatedFromPath(): array;


    /**
     * @deprecated
     * Get the path to the  node
     * 
     * @param string ...$paths
     * 
     * @return self
     */
    public function fromPath(string ...$paths): ?self;


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
     * Restore the initial state
     *
     * @return void
     */
    public function resetState(): self;

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
    public function asJson(int $flags = 0): string;

    /**
     * Reset the state of self instance
     *
     * @return void
     */
    public function reset(): void;
}