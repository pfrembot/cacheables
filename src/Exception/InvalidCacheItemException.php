<?php
/**
 * File InvalidCacheItemException.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Exception;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

class InvalidCacheItemException extends \InvalidArgumentException implements InvalidArgumentException
{
    /**
     * InvalidCacheItemException constructor
     *
     * @param mixed $cacheItem
     */
    public function __construct($cacheItem)
    {
        $type = is_object($cacheItem) ? get_class($cacheItem) : gettype($cacheItem);

        parent::__construct(sprintf('%s does not implement %s', $type, CacheItemInterface::class));
    }
}
