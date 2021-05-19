<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Captcha;

use PHPUnit\Framework\TestCase;
use Zend\Captcha\Exception\InvalidArgumentException;

/**
 * @group      Zend_Captcha
 */
abstract class CommonWordTest extends TestCase
{
    /**
     * Word adapter class name
     *
     * @var string
     */
    protected $wordClass;

    /**
     * @group ZF2-91
     */
    public function testLoadInvalidSessionClass()
    {
        $wordAdapter = new $this->wordClass;
        $wordAdapter->setSessionClass('ZendTest\Captcha\InvalidClassName');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');
        $wordAdapter->getSession();
    }

    public function testErrorMessages()
    {
        $wordAdapter = new $this->wordClass;
        $this->assertFalse($wordAdapter->isValid('foo'));
        $messages = $wordAdapter->getMessages();
        $this->assertNotEmpty($messages);
    }
}
