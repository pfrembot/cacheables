<?php
/**
 * File classes.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */

namespace Pfrembot\Cacheables\Tests\Mocks;

use Epfremme\Collection\Collection;
use Pfrembot\Cacheables\AbstractCacheable;
use Pfrembot\Cacheables\CacheInterface;
use Pfrembot\Identity\IdentityStrategyInterface;
use Pfrembot\Identity\Strategy\IncrementalStrategy;
use Pfrembot\Identity\Strategy\PersistentStrategy;

/**
 * Class PHPCacheKeyTrait
 *
 * @property string $key
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
trait PHPCacheKeyTrait
{
    /**
     * @var IdentityStrategyInterface
     */
    private $generator;

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        if (!$this->key) {
            $this->key = 'php_' . __CLASS__ . $this->getId();
        }

        return $this->key;
    }

    /**
     * Return new incremental ID
     *
     * @return int
     */
    private function getId()
    {
        if (!$this->generator) {
            $this->generator = new PersistentStrategy(new IncrementalStrategy(1));
        }

        try {
            return $this->generator->current();
        } finally {
            $this->generator->next();
        }
    }
}

/**
 * Class SimpleCacheable
 *
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
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

/**
 * Class ComplexCacheable
 *
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
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

/**
 * Class InvalidCacheable
 *
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
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

/**
 * Class MockCache
 *
 * @package Pfrembot\Cacheables\Tests\Mocks
 */
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
