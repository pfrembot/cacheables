<?php
/**
 * File classes.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */

namespace Pfrembot\Cacheables\Tests\Mocks;

use Epfremme\Collection\Collection;
use Pfrembot\Cacheables\CacheableInterface;
use Pfrembot\Identity\IdentityStrategyInterface;
use Pfrembot\Identity\Strategy\IncrementalStrategy;
use Pfrembot\Identity\Strategy\PersistentStrategy;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class PHPCacheKeyTrait
 *
 * @property string $key
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
trait PHPCacheKeyTrait
{
    /**
     * @var IdentityStrategyInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $key;

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        if (!$this->key) {
            $this->key = 'php_' . implode('_', explode('\\', __CLASS__)) . $this->getId();
        }

        return $this->key;
    }

    /**
     * Return new incremental ID
     *
     * @return int
     */
    private function getId()
    {
        if (!$this->generator) {
            $this->generator = new PersistentStrategy(new IncrementalStrategy(1));
        }

        try {
            return $this->generator->current();
        } finally {
            $this->generator->next();
        }
    }
}

/**
 * Class SimpleCacheable
 *
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
class SimpleCacheable implements CacheableInterface
{
    use PHPCacheKeyTrait;

    /**
     * @var mixed|null
     */
    private $data;

    /**
     * SimpleCacheable constructor
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * Return data for the cacheable object
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return array of cacheable child elements
     *
     * @return CacheableInterface[]
     */
    public function getChildren()
    {
        return [];
    }

    /**
     * Return array of cacheable parent elements
     *
     * @return CacheableInterface[]
     */
    public function getParents()
    {
        return [];
    }
}

/**
 * Class ComplexCacheable
 *
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
class ComplexCacheable extends SimpleCacheable
{
    use PHPCacheKeyTrait;

    /**
     * @var SimpleCacheable[]
     */
    private $children;

    /**
     * ComplexCacheable constructor
     *
     * @param mixed $data
     * @param int $count
     */
    public function __construct($data = null, $count = 0)
    {
        $children = is_array($count) ? $count : [];

        for ($i = 0; $i < (int) $count; $i++) {
            array_push($children, new SimpleCacheable($data));
        }

        $this->children = $children;

        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }
}

/**
 * Class InvalidCacheable
 *
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
class InvalidCacheable
{
    /**
     * @var mixed|null
     */
    private $data;

    /**
     * SimpleCacheable constructor
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }
}

///**
// * Class MockCache
// *
// * @package Pfrembot\Cacheables\Tests\Mocks
// */
//class MockCache extends Collection implements CacheItemPoolInterface
//{
//    /**
//     * @var array|CacheItemInterface[]
//     */
//    private $deferred = [];
//
//    /**
//     * {@inheritdoc}
//     */
//    public function exists($key)
//    {
//        return $this->offsetExists($key);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function store($key, $data)
//    {
//        $this->offsetSet($key, $data);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function remove($key)
//    {
//        $this->offsetUnset($key);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getItem($key)
//    {
//        $builder = new \PHPUnit_Framework_MockObject_Generator();
//        $cacheItem = $builder->getMock(CacheItemInterface::class);
//
//
//
//        return $this->offsetGet($key);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getItems(array $keys = array())
//    {
//        return array_map(function($key) {
//            return $this->offsetGet($key);
//        }, $keys, $keys);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function hasItem($key)
//    {
//        return $this->offsetExists($key);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function deleteItem($key)
//    {
//        $this->offsetUnset($key);
//
//        return true;
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function deleteItems(array $keys)
//    {
//        array_walk($keys, function ($key) {
//            $this->offsetUnset($key);
//        });
//
//        return true;
//    }
//
//    /**
//     * Persists a cache item immediately.
//     *
//     * @param CacheItemInterface $item
//     *   The cache item to save.
//     *
//     * @return bool
//     *   True if the item was successfully persisted. False if there was an error.
//     */
//    public function save(CacheItemInterface $item)
//    {
//        $this->offsetSet($item->getKey(), $item->get());
//
//        return true;
//    }
//
//    /**
//     * Sets a cache item to be persisted later.
//     *
//     * @param CacheItemInterface $item
//     *   The cache item to save.
//     *
//     * @return bool
//     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
//     */
//    public function saveDeferred(CacheItemInterface $item)
//    {
//        $this->deferred[$item->getKey()] = $item;
//
//        return true;
//    }
//
//    /**
//     * Persists any deferred cache items.
//     *
//     * @return bool
//     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
//     */
//    public function commit()
//    {
//        foreach ($this->deferred as $item) {
//            $this->save($item);
//        }
//
//        return true;
//    }
//}
