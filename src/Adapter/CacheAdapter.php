<?php
/**
 * File CacheAdapter.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Adapter;

use Pfrembot\Cacheables\CacheInterface;
use Pfrembot\Cacheables\Entity\CacheEntry;

/**
 * Class CacheAdapter
 *
 * @package Pfrembot\Cacheables\Adapter
 */
final class CacheAdapter
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * CacheAdapter constructor
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
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
        return (bool) $this->cache->exists($key);
    }

    /**
     * Return cache object from the cache
     *
     * @param string $key
     * @return CacheEntry|false
     */
    public function get($key)
    {
        if (!$this->exists($key)) {
            return false;
        }

        $data = $this->cache->get($key);

        $cacheEntry = new CacheEntry();
        $cacheEntry->unserialize($data);

        return $cacheEntry;
    }

    /**
     * Store cache object in the cache
     *
     * Also updates existing cached objects with
     * parental and child references
     *
     * @param CacheEntry $cacheEntry
     */
    public function store(CacheEntry $cacheEntry)
    {
        $cacheable = $cacheEntry->getCacheable();
        $cacheKey = $cacheable->getKey();

        if ($this->exists($cacheKey)) {
            $cached = $this->get($cacheKey);

            foreach ($cached->getParents() as $parentKey) {
                $this->remove($parentKey);
            }
        }

        foreach ($cacheable->getChildren() as $child) {
            $entry = new CacheEntry($child, $child->getChildKeys(), [$cacheKey]);
            $entry->setCacheable($child);
            $entry->setParents([$cacheKey]);

            $this->update($entry);
        }

        $this->cache->store($cacheKey, $cacheEntry->serialize());
    }

    /**
     * Remove cached object from the cache
     *
     * Also removes all related cached objects recursively since the
     * cache will need to be reconciled post this operation
     *
     * @param string $key
     */
    public function remove($key)
    {
        $cacheEntry = $this->get($key);

        if (!$cacheEntry) {
            return;
        }

        foreach ($cacheEntry->getParents() as $parentKey) {
            $this->remove($parentKey);
        }

        $this->cache->remove($key);
    }

    /**
     * Update and store a cache object
     *
     * Updates existing cached object if exists or
     * creates a new object if none were found
     *
     * @param CacheEntry $cacheEntry
     */
    private function update(CacheEntry $cacheEntry)
    {
        $cacheable = $cacheEntry->getCacheable();
        $cacheKey = $cacheable->getKey();

        if ($this->exists($cacheKey)) {
            $target = $this->get($cacheKey);
            $target->update($cacheEntry);
        } else {
            $target = $cacheEntry;
        }

        $this->cache->store($cacheKey, $target->serialize());
    }
}
