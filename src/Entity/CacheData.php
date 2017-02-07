<?php
/**
 * File CacheData.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Entity;

use Pfrembot\Cacheables\CacheableInterface;

/**
 * Class CacheData
 *
 * @package Pfrembot\Cacheables\Entity
 */
class CacheData
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string[]
     */
    private $children;

    /**
     * @var string[]
     */
    private $parents;

    /**
     * CacheData constructor
     *
     * @param string $key
     * @param mixed $data
     * @param string[] $children
     * @param string[] $parents
     */
    public function __construct($key, $data, array $children, array $parents)
    {
        $this->key = $key;
        $this->data = $data;
        $this->children = $this->toKeys($children);
        $this->parents = $this->toKeys($parents);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent keys
     *
     * @param array $keys
     * @return $this
     */
    public function setChildren(array $keys = [])
    {
        $this->parents = array_unique(
            array_merge($this->getChildren(), $keys)
        );

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
     * @param array $cacheables
     * @return array|\string[]
     */
    private function toKeys(array $cacheables = [])
    {
        return array_map(function(CacheableInterface $cacheable) {
            return $cacheable->getKey();
        }, $cacheables);
    }
}
