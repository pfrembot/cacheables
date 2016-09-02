<?php
/**
 * File classes.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */

namespace Pfrembot\Cacheables\Tests\Mocks;

use Epfremme\Collection\Collection;
use Pfrembot\Cacheables\AbstractCacheable;
use Pfrembot\Cacheables\CacheableInterface;
use Pfrembot\Cacheables\CacheInterface;

function id($id = 0)
{
    while (true) {
        yield ++$id;
    }
}

/**
 * @property string $key
 */
trait PHPCacheKeyTrait
{
    /**
     * @var \Generator
     */
    private static $generator;

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        if ($this->key) {
            return $this->key;
        }

        $this->key = 'php_' . __CLASS__ . $this->getId();

        return $this->key;
    }

    /**
     * Return new incremental ID
     *
     * @return int
     */
    private function getId()
    {
        if (!static::$generator) {
            static::$generator = id();
        }

        try {
            return static::$generator->current();
        } finally {
            static::$generator->next();
        }
    }
}

class SimpleCacheable extends AbstractCacheable
{
    use PHPCacheKeyTrait;

    /**
     * SimpleCacheable constructor
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}

class ComplexCacheable extends AbstractCacheable
{
    use PHPCacheKeyTrait;

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

        parent::__construct($data, $children);
    }
}

class InvalidCacheable
{
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

class MockCache extends Collection implements CacheInterface
{
    public function exists($key)
    {
        return $this->offsetExists($key);
    }

    public function store($key, $data)
    {
        $this->offsetSet($key, $data);
    }

    public function remove($key)
    {
        $this->offsetUnset($key);
    }
}
