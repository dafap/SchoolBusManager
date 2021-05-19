<?php
/**
 * @see       https://github.com/zendframework/zend-barcode for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-barcode/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Barcode\Object\Exception;

use Zend\Barcode\Exception;

/**
 * Exception for Zend\Barcode component.
 */
class RuntimeException extends Exception\RuntimeException implements
    ExceptionInterface
{
}
