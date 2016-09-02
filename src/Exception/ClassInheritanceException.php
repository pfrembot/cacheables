<?php
/**
 * File ClassInheritanceException.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Exception;

use LogicException;
use Pfrembot\Cacheables\CacheableInterface;

/**
 * Class ClassInheritanceException
 *
 * @package Pfrembot\Cacheables\Exception
 */
class ClassInheritanceException extends LogicException
{
    /**
     * ClassInheritanceException constructor
     *
     * @param string $class
     */
    public function __construct($class)
    {
        parent::__construct(sprintf('Class %s must implement', $class, CacheableInterface::class));
    }
}
