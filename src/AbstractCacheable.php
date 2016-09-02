<?php
/**
 * File AbstractCacheable.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables;

/**
 * Class AbstractCacheable
 *
 * @package Pfrembot\Cacheables
 */
abstract class AbstractCacheable implements CacheableInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var CacheableInterface[]
     */
    private $children;

    /**
     * AbstractCacheable constructor
     *
     * @param mixed $data
     * @param array $children
     * @param null $key
     */
    public function __construct($data = null, array $children = [], $key = null)
    {
        $this->data = $data;
        $this->children = $children;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getKey();

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    final public function getChildren()
    {
        return array_reduce($this->children, function ($result, CacheableInterface $cacheable) {
            return array_merge($result, $cacheable->getChildren());
        }, $this->children);
    }

    /**
     * {@inheritdoc}
     */
    final public function getChildKeys()
    {
        return array_map(function (CacheableInterface $cacheable) {
            return $cacheable->getKey();
        }, $this->getChildren());
    }
}
