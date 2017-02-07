<?php
/**
 * File AbstractCacheableTest.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Tests;

use Pfrembot\Cacheables\AbstractCacheable;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractCacheableTest
 *
 * @package Pfrembot\Cacheables\Tests
 */
class AbstractCacheableTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        /** @var AbstractCacheable $cacheable */
        $cacheable = $this->getMockForAbstractClass(AbstractCacheable::class, ['data', [
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
        ]]);

        $this->assertInstanceOf(AbstractCacheable::class, $cacheable);
        $this->assertAttributeCount(3, 'children', $cacheable);
        $this->assertAttributeContainsOnly(AbstractCacheable::class, 'children', $cacheable);
    }

    public function testGetData()
    {
        /** @var AbstractCacheable $cacheable */
        $cacheable = $this->getMockForAbstractClass(AbstractCacheable::class, ['data']);

        $this->assertEquals('data', $cacheable->getData());
    }

    public function testGetChildren()
    {
        $children = [
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
        ];

        /** @var AbstractCacheable $cacheable */
        $cacheable = $this->getMockForAbstractClass(AbstractCacheable::class, ['data', $children]);

        $this->assertSame($children, $cacheable->getChildKeys());
    }

    public function testGetChildKeys()
    {
        /** @var MockObject[] $children */
        $children = [
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
            $this->getMockForAbstractClass(AbstractCacheable::class, ['data']),
        ];

        /** @var AbstractCacheable $cacheable */
        $cacheable = $this->getMockForAbstractClass(AbstractCacheable::class, ['data', $children]);

        $children[0]->expects($this->once())->method('getKey')->will($this->returnValue(1));
        $children[1]->expects($this->once())->method('getKey')->will($this->returnValue(2));
        $children[2]->expects($this->once())->method('getKey')->will($this->returnValue(3));

        $this->assertEquals([1, 2, 3], $cacheable->getChildKeys());
    }
}
