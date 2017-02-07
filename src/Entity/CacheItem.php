<?php
/**
 * File CacheItem.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Entity;

use Pfrembot\Cacheables\CacheableInterface;
use Psr\Cache\CacheItemInterface;

/**
 * Class CacheItem
 *
 * @package Entity
 */
final class CacheItem implements CacheItemInterface
{
    /**
     * @var CacheItemInterface
     */
    private $innerCacheItem;

    /**
     * @var CacheableInterface
     */
    private $cacheable;

    /**
     * @return mixed
     */
    public function getInner()
    {
        return $this->innerCacheItem;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setInner(CacheItemInterface $data)
    {
        $this->innerCacheItem = $data;

        return $this;
    }

    /**
     * @return CacheableInterface
     */
    public function getCacheable()
    {
        return $this->cacheable;
    }

    /**
     * @param CacheableInterface $cacheable
     * @return $this
     */
    public function setCacheable(CacheableInterface $cacheable)
    {
        $this->cacheable = $cacheable;

        $this->innerCacheItem->set(
            new CacheData($cacheable->getKey(), $cacheable->getData(), $cacheable->getChildren(), $cacheable->getParents())
        );

        return $this;
    }

    /**
     * @return string[]
     */
    public function getParents()
    {
        return $this->get()->getParents();
    }

    /**
     * @param string[] $parents
     * @return $this
     */
    public function setParents(array $parents = [])
    {
        $this->get()->setParents($parents);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getChildren()
    {
        return $this->get()->getChildren();
    }

    /**
     * @param string[] $children
     * @return $this
     */
    public function setChildren(array $children = [])
    {
        $this->get()->setChildren($children);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->innerCacheItem->getKey();
    }

    /**
     * {@inheritdoc}
     *
     * @return CacheData
     */
    public function get()
    {
        return $this->innerCacheItem->get();
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        return $this->innerCacheItem->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        return $this->setCacheable($value);
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        return $this;
    }
}
