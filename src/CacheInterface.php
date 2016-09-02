<?php
/**
 * File CacheInterface.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables;

/**
 * Interface CacheInterface
 *
 * @package Pfrembot\Cacheables
 */
interface CacheInterface
{
    /**
     * Return item from the cache
     *
     * @param string $key
     * @return mixed|false
     */
    public function get($key);

    /**
     * Test if item exists in cache
     *
     * @param string $key
     * @return bool
     */
    public function exists($key);

    /**
     * Store item in cache
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function store($key, $data);

    /**
     * Remove item from cache
     *
     * @param string $key
     * @return void
     */
    public function remove($key);

}
