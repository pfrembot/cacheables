<?php
/**
 * File CacheableInterface.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables;

/**
 * Interface CacheableInterface
 *
 * @package Pfrembot\Cacheables
 */
interface CacheableInterface
{
    /**
     * Return unique cache key for the cacheable object
     *
     * Specific object being cached and is used to expire stale
     * versions of the object within the cache
     *
     * @return string
     */
    public function getKey();

    /**
     * Return data for the cacheable object
     *
     * @return mixed
     */
    public function getData();

    /**
     * Return array of cacheable child elements
     *
     * @return CacheableInterface[]
     */
    public function getChildren();

    /**
     * Return array of cacheable parent elements
     *
     * @return CacheableInterface[]
     */
    public function getParents();
}
