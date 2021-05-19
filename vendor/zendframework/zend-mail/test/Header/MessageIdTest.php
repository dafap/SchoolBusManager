<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Header\MessageId<extended>
 */
class MessageIdTest extends TestCase
{
    public function testSettingManually()
    {
        $id = "CALTvGe4_oYgf9WsYgauv7qXh2-6=KbPLExmJNG7fCs9B=1nOYg@mail.example.com";
        $messageid = new Header\MessageId();
        $messageid->setId($id);

        $expected = sprintf('<%s>', $id);
        $this->assertEquals($expected, $messageid->getFieldValue());
    }

    public function testAutoGeneration()
    {
        $messageid = new Header\MessageId();
        $messageid->setId();

        $this->assertContains('@', $messageid->getFieldValue());
    }


    public function headerLines()
    {
        return [
            'newline'      => ["Message-ID: foo\nbar"],
            'cr-lf'        => ["Message-ID: bar\r\nfoo"],
            'cr-lf-wsp'    => ["Message-ID: bar\r\n\r\n baz"],
            'multiline'    => ["Message-ID: baz\r\nbar\r\nbau"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringPreventsCrlfInjectionOnDetection($header)
    {
        $this->expectException('Zend\Mail\Header\Exception\InvalidArgumentException');
        $messageid = Header\MessageId::fromString($header);
    }

    public function invalidIdentifiers()
    {
        return [
            'newline'      => ["foo\nbar"],
            'cr-lf'        => ["bar\r\nfoo"],
            'cr-lf-wsp'    => ["bar\r\n\r\n baz"],
            'multiline'    => ["baz\r\nbar\r\nbau"],
            'folding'      => ["bar\r\n baz"],
        ];
    }

    /**
     * @dataProvider invalidIdentifiers
     * @group ZF2015-04
     */
    public function testInvalidIdentifierRaisesException($id)
    {
        $header = new Header\MessageId();
        $this->expectException('Zend\Mail\Header\Exception\InvalidArgumentException');
        $header->setId($id);
    }
}
