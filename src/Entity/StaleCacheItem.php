<?php
/**
 * File StaleCacheItem.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Entity;

/**
 * Class StaleCacheItem
 *
 * @package Pfrembot\Cacheables\Entity
 */
final class StaleCacheItem
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string[]
     */
    private $children = [];

    /**
     * @var string[]
     */
    private $parents = [];

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param string[] $children
     * @return $this
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * @param string[] $parents
     * @return $this
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;

        return $this;
    }
}
