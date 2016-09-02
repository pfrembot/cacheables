<?php
/**
 * File CacheAdapterTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Tests\Adapter;

use Pfrembot\Cacheables\Adapter\CacheAdapter;
use Pfrembot\Cacheables\CacheableInterface;
use Pfrembot\Cacheables\Entity\CacheEntry;
use Pfrembot\Cacheables\Tests\Mocks\ComplexCacheable;
use Pfrembot\Cacheables\Tests\Mocks\MockCache;
use Pfrembot\Cacheables\Tests\Mocks\SimpleCacheable;
use PHPUnit_Framework_TestCase;

/**
 * Class CacheAdapterTest
 *
 * @package Pfrembot\Cacheables\Tests\Adapter
 */
class CacheAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MockCache
     */
    private $cache;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->cache = new MockCache();
    }

    public function testConstruct()
    {
        $adapter = new CacheAdapter($this->cache);

        $this->assertInstanceOf(CacheAdapter::class, $adapter);
        $this->assertAttributeSame($this->cache, 'cache', $adapter);
    }

    public function testExists()
    {
        $adapter = new CacheAdapter($this->cache);
        $this->cache->set('foo', 'bar');

        $this->assertTrue($adapter->exists('foo'));
    }

    public function testGet()
    {
        $adapter = new CacheAdapter($this->cache);
        $this->cache->set('foo', serialize([
            'key' => 'foo',
            'data' => 'bar',
            'class' => SimpleCacheable::class,
            'parents' => [4,5,6]
        ]));

        $cacheEntry = $adapter->get('foo');

        $this->assertInstanceOf(CacheEntry::class, $cacheEntry);
        $this->assertInstanceOf(CacheableInterface::class, $cacheEntry->getCacheable());
        $this->assertInstanceOf(SimpleCacheable::class, $cacheEntry->getCacheable());

        $this->assertEquals('bar', $cacheEntry->getCacheable()->getData());
        $this->assertEquals([4,5,6], $cacheEntry->getParents());
    }

    public function testGetMissing()
    {
        $adapter = new CacheAdapter($this->cache);

        $this->assertFalse($adapter->get('foo'));
    }

    public function testStore()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable(new SimpleCacheable());
        $cacheEntry->setParents([4,5,6]);

        $adapter->store($cacheEntry);

        $key = $cacheEntry->getCacheable()->getKey();

        $this->assertTrue($this->cache->offsetExists($key));
        $this->assertEquals($cacheEntry->serialize(), $this->cache->offsetGet($key));
    }

    public function testStoreWithChildren()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable(new ComplexCacheable('data', 3));
        $adapter->store($cacheEntry);

        $cacheable = $cacheEntry->getCacheable();

        $this->assertCount(4, $this->cache);
        $this->assertTrue($this->cache->offsetExists($cacheable->getKey()));

        $cached = $adapter->get($cacheable->getKey());

        $this->assertInstanceOf(CacheEntry::class, $cacheEntry);
        $this->assertInstanceOf(ComplexCacheable::class, $cacheEntry->getCacheable());
        $this->assertCount(0, $cached->getParents());

        foreach ($cacheable->getChildKeys() as $key) {
            $this->assertTrue($this->cache->offsetExists($key));

            $cached = $adapter->get($key);

            $this->assertInstanceOf(CacheEntry::class, $cacheEntry);
            $this->assertInstanceOf(ComplexCacheable::class, $cacheEntry->getCacheable());
            $this->assertCount(1, $cached->getParents());
            $this->assertEquals($cacheable->getKey(), current($cached->getParents()));
        }
    }

    public function testStoreWithExistingChildren()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable(new ComplexCacheable('data', 3));

        $cacheable = $cacheEntry->getCacheable();
        $children = $cacheable->getChildren();

        $adapter->store((new CacheEntry())->setCacheable($children[0]));
        $adapter->store((new CacheEntry())->setCacheable($children[1]));

        $this->assertCount(0, $adapter->get($children[0]->getKey())->getParents());
        $this->assertCount(0, $adapter->get($children[1]->getKey())->getParents());
        $this->assertFalse($adapter->exists($children[2]->getKey()));

        $adapter->store($cacheEntry);

        $this->assertCount(4, $this->cache);
        $this->assertTrue($this->cache->offsetExists($cacheable->getKey()));

        $cached = $adapter->get($cacheable->getKey());

        $this->assertInstanceOf(CacheEntry::class, $cacheEntry);
        $this->assertInstanceOf(ComplexCacheable::class, $cacheEntry->getCacheable());
        $this->assertCount(0, $cached->getParents());

        foreach ($cacheable->getChildKeys() as $key) {
            $this->assertTrue($this->cache->offsetExists($key));

            $cached = $adapter->get($key);

            $this->assertInstanceOf(CacheEntry::class, $cacheEntry);
            $this->assertInstanceOf(ComplexCacheable::class, $cacheEntry->getCacheable());
            $this->assertCount(1, $cached->getParents());
            $this->assertEquals($cacheable->getKey(), current($cached->getParents()));
        }
    }

    public function testStoreWithParents()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable(new ComplexCacheable('data', 3));

        $cacheable = $cacheEntry->getCacheable();
        $children = $cacheable->getChildren();

        $adapter->store($cacheEntry);
        $adapter->store((new CacheEntry())->setCacheable($children[0]));

        $this->assertFalse($adapter->get($cacheEntry->getCacheable()->getKey()));
    }

    public function testRemove()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheable = new ComplexCacheable('data', 3);
        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable($cacheable);

        $adapter->store($cacheEntry);

        $this->assertCount(4, $this->cache);

        $adapter->remove($cacheable->getKey());

        $this->assertCount(3, $this->cache);
        $this->assertFalse($adapter->exists($cacheable->getKey()));
    }

    public function testRemoveChild()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheable = new ComplexCacheable('data', 3);
        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable($cacheable);

        $adapter->store($cacheEntry);

        $this->assertCount(4, $this->cache);

        $child = current($cacheable->getChildren());

        $adapter->remove($child->getKey());

        $this->assertCount(2, $this->cache);
        $this->assertFalse($adapter->exists($child->getKey()));
        $this->assertFalse($adapter->exists($cacheable->getKey()));
    }

    public function testRemoveMissing()
    {
        $adapter = new CacheAdapter($this->cache);

        $cacheable = new ComplexCacheable('data', 3);
        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable($cacheable);

        $adapter->store($cacheEntry);
        $this->assertCount(4, $this->cache);

        $adapter->remove('noop');
        $this->assertCount(4, $this->cache);
    }
}
