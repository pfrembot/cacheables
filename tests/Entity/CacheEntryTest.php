<?php
/**
 * File CacheEntryTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Tests\Entity;

use Pfrembot\Cacheables\Entity\CacheEntry;
use Pfrembot\Cacheables\Tests\Mocks\ComplexCacheable;
use Pfrembot\Cacheables\Tests\Mocks\SimpleCacheable;
use PHPUnit_Framework_TestCase;

/**
 * Class CacheEntryTest
 *
 * @package Pfrembot\Cacheables\Tests\Entity
 */
class CacheEntryTest extends PHPUnit_Framework_TestCase
{
    public function testSetCacheable()
    {
        $cacheable = new SimpleCacheable();
        $cacheEntry = new CacheEntry();

        $this->assertSame($cacheEntry, $cacheEntry->setCacheable($cacheable));
        $this->assertAttributeSame($cacheable, 'cacheable', $cacheEntry);
    }

    public function testGetCacheable()
    {
        $cacheable = new SimpleCacheable();
        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable($cacheable);

        $this->assertSame($cacheable, $cacheEntry->getCacheable());
    }

    public function testSetParents()
    {
        $cacheEntry = new CacheEntry();

        $this->assertSame($cacheEntry, $cacheEntry->setParents(['foo', 'bar', 'baz']));

        $this->assertAttributeCount(3, 'parents', $cacheEntry);
        $this->assertAttributeEquals(['foo', 'bar', 'baz'], 'parents', $cacheEntry);

        $this->assertSame($cacheEntry, $cacheEntry->setParents(['qux', 'bar']));

        $this->assertAttributeCount(4, 'parents', $cacheEntry);
        $this->assertAttributeEquals(['foo', 'bar', 'baz', 'qux'], 'parents', $cacheEntry);
    }

    public function testGetParents()
    {
        $cacheEntry = new CacheEntry();
        $cacheEntry->setParents(['foo', 'bar', 'baz']);

        $this->assertEquals(['foo', 'bar', 'baz'], $cacheEntry->getParents());
    }

    public function testUpdate()
    {
        $cacheable = new SimpleCacheable('old');
        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable($cacheable);
        $cacheEntry->setParents(['baz1']);

        $cacheable = new ComplexCacheable('new', 2);
        $newEntry = new CacheEntry();
        $newEntry->setCacheable($cacheable);
        $newEntry->setParents(['baz2']);

        $cacheEntry->update($newEntry);

        $this->assertSame($cacheable, $cacheEntry->getCacheable());
        $this->assertEquals(['baz1', 'baz2'], $cacheEntry->getParents());
    }

    public function testSerialize()
    {
        $cacheable = new ComplexCacheable('data', 2);
        $cacheEntry = new CacheEntry();
        $cacheEntry->setCacheable($cacheable);
        $cacheEntry->setParents(['baz']);

        $expected = serialize([
            'key' => $cacheable->getKey(),
            'data' => $cacheable->getData(),
            'class' => ComplexCacheable::class,
            'parents' => ['baz'],
        ]);

        $this->assertEquals($expected, $cacheEntry->serialize());
    }

    public function testDeserialize()
    {
        $serialized = 'a:5:{s:3:"key";s:52:"php_Pfrembot\Cacheables\Tests\Mocks\SimpleCacheable1";s:4:"data";s:4:"data";s:5:"class";s:47:"Pfrembot\Cacheables\Tests\Mocks\SimpleCacheable";s:7:"parents";a:1:{i:0;s:3:"baz";}s:8:"children";a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}}';

        $cacheable = new SimpleCacheable('data', [], 'php_Pfrembot\Cacheables\Tests\Mocks\SimpleCacheable1');

        $cacheEntry = new CacheEntry();
        $cacheEntry->unserialize($serialized);

        $this->assertEquals($cacheable, $cacheEntry->getCacheable());
        $this->assertEquals(['baz'], $cacheEntry->getParents());
    }

    /**
     * @expectedException \Pfrembot\Cacheables\Exception\ClassNotFoundException
     */
    public function testDeserializeClassNotFoundException()
    {
        $serialized = 'a:5:{s:3:"key";s:53:"php_Pfrembot\Cacheables\Tests\Mocks\MissingCacheable1";s:4:"data";s:4:"data";s:5:"class";s:48:"Pfrembot\Cacheables\Tests\Mocks\MissingCacheable";s:7:"parents";a:1:{i:0;s:3:"baz";}s:8:"children";a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}}';

        $cacheEntry = new CacheEntry();
        $cacheEntry->unserialize($serialized);
    }

    /**
     * @expectedException \Pfrembot\Cacheables\Exception\ClassInheritanceException
     */
    public function testDeserializeClassInheritanceException()
    {
        $serialized = 'a:5:{s:3:"key";s:53:"php_Pfrembot\Cacheables\Tests\Mocks\InvalidCacheable1";s:4:"data";s:4:"data";s:5:"class";s:48:"Pfrembot\Cacheables\Tests\Mocks\InvalidCacheable";s:7:"parents";a:1:{i:0;s:3:"baz";}s:8:"children";a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}}';

        $cacheEntry = new CacheEntry();
        $cacheEntry->unserialize($serialized);
    }
}
