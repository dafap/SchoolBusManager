<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Mail\Header;

use PHPUnit\Framework\TestCase;
use Zend\Mail\Header\HeaderWrap;
use Zend\Mail\Storage;

/**
 * @group      Zend_Mail
 * @covers Zend\Mail\Header\HeaderWrap<extended>
 */
class HeaderWrapTest extends TestCase
{
    public function testWrapUnstructuredHeaderAscii()
    {
        $string = str_repeat('foobarblahblahblah baz bat', 4);
        $header = $this->createMock('Zend\Mail\Header\UnstructuredInterface');
        $header->expects($this->any())
            ->method('getEncoding')
            ->will($this->returnValue('ASCII'));
        $expected = wordwrap($string, 78, "\r\n ");

        $test = HeaderWrap::wrap($string, $header);
        $this->assertEquals($expected, $test);
    }

    /**
     * @group ZF2-258
     */
    public function testWrapUnstructuredHeaderMime()
    {
        $string = str_repeat('foobarblahblahblah baz bat', 3);
        $header = $this->createMock('Zend\Mail\Header\UnstructuredInterface');
        $header->expects($this->any())
            ->method('getEncoding')
            ->will($this->returnValue('UTF-8'));
        $expected = "=?UTF-8?Q?foobarblahblahblah=20baz=20batfoobarblahblahblah=20baz=20?=\r\n"
                    . " =?UTF-8?Q?batfoobarblahblahblah=20baz=20bat?=";

        $test = HeaderWrap::wrap($string, $header);
        $this->assertEquals($expected, $test);
        $this->assertEquals($string, iconv_mime_decode($test, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'));
    }

    /**
     * @group ZF2-359
     */
    public function testMimeEncoding()
    {
        $string   = 'Umlauts: ä';
        $expected = '=?UTF-8?Q?Umlauts:=20=C3=A4?=';

        $test = HeaderWrap::mimeEncodeValue($string, 'UTF-8', 78);
        $this->assertEquals($expected, $test);
        $this->assertEquals($string, iconv_mime_decode($test, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8'));
    }

    public function testMimeDecoding()
    {
        $expected = str_repeat('foobarblahblahblah baz bat', 3);
        $encoded = "=?UTF-8?Q?foobarblahblahblah=20baz=20batfoobarblahblahblah=20baz=20?=\r\n"
                    . " =?UTF-8?Q?batfoobarblahblahblah=20baz=20bat?=";

        $decoded = HeaderWrap::mimeDecodeValue($encoded);

        $this->assertEquals($expected, $decoded);
    }

    /**
     * Test that header lazy-loading doesn't break later header access
     * because undocumented behavior in iconv_mime_decode()
     * @see https://github.com/zendframework/zend-mail/pull/187
     */
    public function testMimeDecodeBreakageBug()
    {
        $headerValue = 'v=1; a=rsa-sha25; c=relaxed/simple; d=example.org; h='
            . "\r\n\t" . 'content-language:content-type:content-type:in-reply-to';
        $headers = "DKIM-Signature: {$headerValue}";

        $message = new Storage\Message(['headers' => $headers, 'content' => 'irrelevant']);
        $headers = $message->getHeaders();
        // calling toString will lazy load all headers
        // and would break DKIM-Signature header access
        $headers->toString();

        $header = $headers->get('DKIM-Signature');
        $this->assertEquals(
            'v=1; a=rsa-sha25; c=relaxed/simple; d=example.org;'
            . ' h= content-language:content-type:content-type:in-reply-to',
            $header->getFieldValue()
        );
    }

    /**
     * Test that fails with HeaderWrap::canBeEncoded at lowest level:
     *   iconv_mime_encode(): Unknown error (7)
     *
     * which can be triggered as:
     *   $header = new GenericHeader($name, $value);
     */
    public function testCanBeEncoded()
    {
        // @codingStandardsIgnoreStart
        $value   = "[#77675] New Issue:xxxxxxxxx xxxxxxx xxxxxxxx xxxxxxxxxxxxx xxxxxxxxxx xxxxxxxx, tähtaeg xx.xx, xxxx";
        // @codingStandardsIgnoreEnd
        //
        $res = HeaderWrap::canBeEncoded($value);
        $this->assertTrue($res);
    }

    /**
     * @requires extension imap
     */
    public function testMultilineWithMultibyteSplitAcrossCharacter()
    {
        $originalValue = 'аф';

        $this->assertEquals(strlen($originalValue), 4);

        $part1 = base64_encode(substr($originalValue, 0, 3));
        $part2 = base64_encode(substr($originalValue, 3));

        $header = '=?utf-8?B?' . $part1 . '?==?utf-8?B?' . $part2 . '?=';

        $this->assertEquals(
            $originalValue,
            HeaderWrap::mimeDecodeValue($header)
        );
    }
}
