<?php
/**
 * File ClassNotFoundException.php
 *
 * @author Edward Pfremmer <epfremme@nerdery.com>
 */
namespace Pfrembot\Cacheables\Exception;

use LogicException;

/**
 * Class ClassNotFoundException
 *
 * @package Pfrembot\Cacheables\Exception
 */
class ClassNotFoundException extends LogicException
{
    /**
     * ClassNotFoundException constructor
     *
     * @param string $class
     */
    public function __construct($class)
    {
        parent::__construct(sprintf('Class %s not found', $class));
    }
}
