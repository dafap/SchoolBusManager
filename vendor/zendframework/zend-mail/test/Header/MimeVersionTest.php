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
 * @covers Zend\Mail\Header\MimeVersion<extended>
 */
class MimeVersionTest extends TestCase
{
    public function testSettingManually()
    {
        $version = "2.0";
        $mime = new Header\MimeVersion();
        $mime->setVersion($version);
        $this->assertEquals($version, $mime->getFieldValue());
    }

    public function testDefaultVersion()
    {
        $mime = new Header\MimeVersion();
        $this->assertEquals('1.0', $mime->getVersion());
    }

    public function headerLines()
    {
        return [
            'newline'      => ["MIME-Version: 5.0\nbar"],
            'cr-lf'        => ["MIME-Version: 2.0\r\n"],
            'cr-lf-wsp'    => ["MIME-Version: 3\r\n\r\n.1"],
            'multiline'    => ["MIME-Version: baz\r\nbar\r\nbau"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringRaisesExceptionOnDetectionOfCrlfInjection($header)
    {
        $this->expectException('Zend\Mail\Header\Exception\InvalidArgumentException');
        $mime = Header\MimeVersion::fromString($header);
    }

    public function invalidVersions()
    {
        return [
            'no-decimal'    => ['1'],
            'multi-decimal' => ['1.0.0'],
            'alpha'         => ['X.Y'],
            'non-alnum'     => ['Version 1.0'],
        ];
    }

    /**
     * @dataProvider invalidVersions
     * @group ZF2015-04
     */
    public function testRaisesExceptionOnInvalidVersionFromSetVersion($value)
    {
        $header = new Header\MimeVersion();
        $this->expectException('Zend\Mail\Header\Exception\InvalidArgumentException');
        $header->setVersion($value);
    }
}
