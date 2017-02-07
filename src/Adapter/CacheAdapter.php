<?php
/**
 * File CacheAdapter.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Adapter;

use Exception\InvalidCacheItemException;
use Pfrembot\Cacheables\CacheableInterface;
use Pfrembot\Cacheables\Entity\CacheData;
use Pfrembot\Cacheables\Entity\CacheItem;
use Pfrembot\Cacheables\Entity\StaleCacheItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CacheAdapter
 *
 * @package Pfrembot\Cacheables\Adapter
 */
final class CacheAdapter
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * CacheAdapter constructor
     *
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Test if the cache key exists
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->cache->hasItem($key);
    }

    /**
     * Return cache object from the cache
     *
     * @param string $key
     * @return CacheItem
     */
    public function get($key)
    {
        $innerItem = $this->cache->getItem($key);

        $cacheItem = new CacheItem();
        $cacheItem->setInner($innerItem);

        return $cacheItem;
    }

    /**
     * Store cache object in the cache
     *
     * Also updates existing cached objects with
     * parental and child references
     *
     * @param CacheItemInterface $cacheItem
     * @return bool
     */
    public function store($cacheItem)
    {
        if (!$cacheItem instanceof CacheItemInterface) {
            throw new InvalidCacheItemException($cacheItem);
        }

        if (!$cacheItem instanceof CacheItem) {
            return $this->cache->saveDeferred($cacheItem);
        }

        if ($cacheItem->isHit()) {
            foreach ($cacheItem->getParents() as $parent) {
                $this->invalidate($parent);
            }
        }

        if ($cacheable = $cacheItem->getCacheable()) {
            foreach ($cacheable->getChildren() as $child) {
                $this->update($child, $cacheable);
            }
        }

        return $this->cache->saveDeferred($cacheItem->getInner());
    }

    /**
     * @param CacheableInterface $cacheable
     */
    private function update(CacheableInterface $cacheable, CacheableInterface $parent)
    {
        $cacheItem = $this->get($cacheable->getKey());
        $cacheItem->setCacheable($cacheable);

        if (!in_array($parent->getKey(), $cacheItem->getParents())) {
            $cacheItem->setParents([$parent->getKey()]);
        }

        foreach ($cacheable->getChildren() as $child) {
            $this->update($child, $cacheable);
        }

        $this->cache->saveDeferred($cacheItem->getInner());
    }

    /**
     * Remove cached object from the cache
     *
     * Also removes all related cached objects recursively since the
     * cache will need to be reconciled post this operation
     *
     * @param string $key
     * @return bool
     */
    public function remove($key)
    {
        $cacheItem = $this->get($key);

        if (!$cacheItem->isHit()) {
            return false;
        }

        return $this->invalidate($cacheItem->getKey());
    }

    /**
     * Replace existing cache item with stale cache entry
     *
     * Stale entry can be used later to re-prime the cache with
     * fresh data based on potential cached child item data
     *
     * @param string $key
     * @return bool
     */
    private function invalidate($key)
    {
        $cacheItem = $this->get($key);

        if (!$cacheItem->isHit()) {
            return false;
        }

        foreach ($cacheItem->getParents() as $parent) {
            $this->invalidate($parent);
        }

        $staleItem = new StaleCacheItem();
        $staleItem->setKey($cacheItem->getKey());
        $staleItem->setParents($cacheItem->getParents());
        $staleItem->setChildren($cacheItem->getChildren());

        $cacheItem->set($staleItem);

        return $this->cache->saveDeferred($cacheItem);
    }
}
