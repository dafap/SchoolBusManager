<?php
/**
 * @see       https://github.com/zendframework/zend-barcode for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-barcode/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Barcode;

use PHPUnit\Framework\TestCase;
use Zend\Barcode\Exception\InvalidArgumentException;
use Zend\Barcode\Object\AbstractObject;
use Zend\Barcode\ObjectPluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

class ObjectPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new ObjectPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf()
    {
        return AbstractObject::class;
    }
}
