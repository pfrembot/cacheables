<?php
/**
 * File CacheEntry.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Entity;

use Pfrembot\Cacheables\CacheableInterface;
use Pfrembot\Cacheables\Exception\ClassInheritanceException;
use Pfrembot\Cacheables\Exception\ClassNotFoundException;
use Serializable;

final class CacheEntry implements Serializable
{
    /**
     * @var CacheableInterface
     */
    private $cacheable;

    /**
     * @var array|string[]
     */
    private $parents = [];

    /**
     * Return the current cacheable object
     *
     * @return CacheableInterface
     */
    public function getCacheable()
    {
        return $this->cacheable;
    }

    /**
     * Set cacheable object
     *
     * @param CacheableInterface $cacheable
     * @return $this
     */
    public function setCacheable(CacheableInterface $cacheable)
    {
        $this->cacheable = $cacheable;

        return $this;
    }

    /**
     * Return parent keys
     *
     * @return array|\string[]
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Set parent keys
     *
     * @param array $keys
     * @return $this
     */
    public function setParents(array $keys = [])
    {
        $this->parents = array_unique(
            array_merge($this->getParents(), $keys)
        );

        return $this;
    }

    /**
     * @param CacheEntry $cacheEntry
     * @return $this
     */
    public function update(CacheEntry $cacheEntry)
    {
        $this->setCacheable($cacheEntry->getCacheable());
        $this->setParents($cacheEntry->getParents());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            'key' => $this->cacheable->getKey(),
            'data' => $this->cacheable->getData(),
            'class' => get_class($this->cacheable),
            'parents' => $this->getParents(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $class = $data['class'];

        if (!class_exists($class)) {
            throw new ClassNotFoundException($class);
        }

        if (!is_subclass_of($class, CacheableInterface::class)) {
            throw new ClassInheritanceException($class);
        }

        $cacheable = new $class($data['data'], [], $data['key']);

        $this->setCacheable($cacheable);
        $this->setParents($data['parents']);
    }
}
