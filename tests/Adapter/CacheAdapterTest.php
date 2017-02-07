<?php
/**
 * File CacheAdapterTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Tests\Adapter;

use Closure;
use Pfrembot\Cacheables\Adapter\CacheAdapter;
use Pfrembot\Cacheables\CacheableInterface;
use Pfrembot\Cacheables\Entity\CacheData;
use Pfrembot\Cacheables\Entity\CacheItem;
use Pfrembot\Cacheables\Tests\Mocks\ComplexCacheable;
use Pfrembot\Cacheables\Tests\Mocks\SimpleCacheable;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Class CacheAdapterTest
 *
 * @package Pfrembot\Cacheables\Tests\Adapter
 */
class CacheAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayAdapter
     */
    private $cache;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->cache = new ArrayAdapter();
    }

    /**
     * @param array|CacheableInterface[] $cacheables
     * @return array|string[]
     */
    private function getKeys(array $cacheables = [])
    {
        return array_map(function (CacheableInterface $cacheable) {
            return $cacheable->getKey();
        }, $cacheables);
    }

    /**
     * @group adapter
     */
    public function testConstruct()
    {
        $adapter = new CacheAdapter($this->cache);

        $this->assertInstanceOf(CacheAdapter::class, $adapter);
        $this->assertAttributeSame($this->cache, 'cache', $adapter);
    }

    /**
     * @group adapter
     */
    public function testExists()
    {
        $adapter = new CacheAdapter($this->cache);

        $this->cache->save(
            $this->cache->getItem('foo')->set('bar')
        );

        $this->assertTrue($adapter->exists('foo'));
    }

    /**
     * @group adapter
     */
    public function testGet()
    {
        $adapter = new CacheAdapter($this->cache);

        $parent = new SimpleCacheable();
        $child = new SimpleCacheable();

        $this->cache->save(
            $this->cache->getItem('foo')->set(new CacheData('foo', 'bar', [$child], [$parent]))
        );

        $cacheItem = $adapter->get('foo');

        $this->assertInstanceOf(CacheItem::class, $cacheItem);
        $this->assertInstanceOf(CacheData::class, $cacheItem->get());

        $this->assertEquals('bar', $cacheItem->get()->getData());
        $this->assertEquals([$parent->getKey()], $cacheItem->get()->getParents());
        $this->assertEquals([$parent->getKey()], $cacheItem->getParents());
        $this->assertEquals([$child->getKey()], $cacheItem->get()->getChildren());
        $this->assertEquals([$child->getKey()], $cacheItem->getChildren());
    }

    /**
     * @group adapter
     */
    public function testGetMissing()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheItem = $adapter->get('foo');

        $this->assertInstanceOf(CacheItem::class, $cacheItem);
        $this->assertNull($cacheItem->get());
        $this->assertFalse($cacheItem->isHit());
    }

    /**
     * @group adapter
     */
    public function testStore()
    {
        $adapter = new CacheAdapter($this->cache);
        $cacheable = new SimpleCacheable();

        $cacheItem = $adapter->get($cacheable->getKey());
        $cacheItem->setCacheable($cacheable);
        $cacheItem->setParents([4,5,6]);

        $adapter->store($cacheItem);

        $key = $cacheItem->getKey();
        $cachedItem = $this->cache->getItem($key);

        $this->assertTrue($this->cache->hasItem($key));
        $this->assertEquals($cacheItem->getInner()->get(), $cachedItem->get());
        $this->assertEquals([], $cachedItem->get()->getChildren());
        $this->assertEquals([4,5,6], $cachedItem->get()->getParents());
    }

    /**
     * @group adapter
     */
    public function testStoreWithChildren()
    {
        $adapter = new CacheAdapter($this->cache);
        $cacheable = new ComplexCacheable('data', 3);

        $cacheItem = $adapter->get($cacheable->getKey());
        $cacheItem->setCacheable($cacheable);
        $adapter->store($cacheItem);

        $cachedItem = $this->cache->getItem($cacheable->getKey());

        $this->assertInstanceOf(CacheData::class, $cachedItem->get());
        $this->assertEquals($cacheItem->getInner()->get(), $cachedItem->get());
        $this->assertEquals($this->getKeys($cacheable->getChildren()), $cachedItem->get()->getChildren());

        foreach ($cacheable->getChildren() as $child) {
            $this->assertTrue($this->cache->hasItem($child->getKey()));

            $cached = $this->cache->getItem($child->getKey());

            $this->assertInstanceOf(CacheData::class, $cached->get());
            $this->assertEquals($child->getData(), $cached->get()->getData());
            $this->assertEquals([$cacheable->getKey()], $cached->get()->getParents());
        }
    }

    public function testStoreWithExistingChildren()
    {
        $adapter = new CacheAdapter($this->cache);
        $cacheable = new ComplexCacheable('data', 3);

        $cacheItem = $adapter->get($cacheable->getKey());
        $cacheItem->setCacheable($cacheable);
        $adapter->store($cacheItem);

        $children = $cacheable->getChildren();

        $adapter->store($adapter->get($children[0]->getKey())->setCacheable($children[0]));
        $adapter->store($adapter->get($children[1]->getKey())->setCacheable($children[1]));

        $this->assertCount(0, $adapter->get($children[0]->getKey())->getParents());
        $this->assertCount(0, $adapter->get($children[1]->getKey())->getParents());
        $this->assertFalse($adapter->exists($children[2]->getKey()));

        $adapter->store($cacheItem);

        $this->assertTrue($this->cache->hasItem($cacheable->getKey()));

        $cached = $adapter->get($cacheable->getKey());

        $this->assertInstanceOf(CacheItem::class, $cached);
        $this->assertInstanceOf(CacheData::class, $cached->get());
        $this->assertCount(3, $cached->getChildren());
        $this->assertCount(0, $cached->getParents());

        foreach ($cached->getChildren() as $key) {
            $this->assertTrue($this->cache->hasItem($key));

            $cached = $adapter->get($key);

            $this->assertInstanceOf(CacheItem::class, $cached);
            $this->assertInstanceOf(CacheData::class, $cached->get());
            $this->assertCount(1, $cached->getParents());
            $this->assertEquals($cacheable->getKey(), current($cached->getParents()));
        }
    }
//
//    public function testStoreWithParents()
//    {
//        $adapter = new CacheAdapter($this->cache);
//
//        $cacheEntry = new CacheEntry();
//        $cacheEntry->setCacheable(new ComplexCacheable('data', 3));
//
//        $cacheable = $cacheEntry->getCacheable();
//        $children = $cacheable->getChildKeys();
//
//        $adapter->store($cacheEntry);
//        $adapter->store((new CacheEntry())->setCacheable($children[0]));
//
//        $this->assertFalse($adapter->get($cacheEntry->getCacheable()->getKey()));
//    }
//
//    public function testRemove()
//    {
//        $adapter = new CacheAdapter($this->cache);
//
//        $cacheable = new ComplexCacheable('data', 3);
//        $cacheEntry = new CacheEntry();
//        $cacheEntry->setCacheable($cacheable);
//
//        $adapter->store($cacheEntry);
//
//        $this->assertCount(4, $this->cache);
//
//        $adapter->remove($cacheable->getKey());
//
//        $this->assertCount(3, $this->cache);
//        $this->assertFalse($adapter->exists($cacheable->getKey()));
//    }
//
//    public function testRemoveChild()
//    {
//        $adapter = new CacheAdapter($this->cache);
//
//        $cacheable = new ComplexCacheable('data', 3);
//        $cacheEntry = new CacheEntry();
//        $cacheEntry->setCacheable($cacheable);
//
//        $adapter->store($cacheEntry);
//
//        $this->assertCount(4, $this->cache);
//
//        $child = current($cacheable->getChildKeys());
//
//        $adapter->remove($child->getKey());
//
//        $this->assertCount(2, $this->cache);
//        $this->assertFalse($adapter->exists($child->getKey()));
//        $this->assertFalse($adapter->exists($cacheable->getKey()));
//    }
//
//    public function testRemoveMissing()
//    {
//        $adapter = new CacheAdapter($this->cache);
//
//        $cacheable = new ComplexCacheable('data', 3);
//        $cacheEntry = new CacheEntry();
//        $cacheEntry->setCacheable($cacheable);
//
//        $adapter->store($cacheEntry);
//        $this->assertCount(4, $this->cache);
//
//        $adapter->remove('noop');
//        $this->assertCount(4, $this->cache);
//    }
}
