<?php
/**
 * @see       https://github.com/zendframework/zend-debug for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-debug/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Debug;

use Zend\Debug\Debug;
use Zend\Escaper\Escaper;

/**
 * @group      Zend_Debug
 */
use PHPUnit\Framework\TestCase;

class DebugTest extends TestCase
{
    public function testDebugDefaultSapi()
    {
        $sapi = php_sapi_name();
        Debug::setSapi(null);
        $data = 'string';
        $result = Debug::dump($data, null, false);
        $this->assertEquals($sapi, Debug::getSapi());
    }

    public function testDebugDump()
    {
        Debug::setSapi('cli');
        $data = 'string';
        $result = Debug::dump($data, null, false);
        $result = str_replace([PHP_EOL, "\n"], '_', $result);
        $expected = '__string(6) "string"__';
        $this->assertEquals($expected, $result);
    }

    public function testDebugCgi()
    {
        Debug::setSapi('cgi');
        $data = 'string';
        $result = Debug::dump($data, null, false);

        // Has to check for two strings, because xdebug internally handles CLI vs Web
        $this->assertContains(
            $result,
            [
                "<pre>string(6) \"string\"\n</pre>",
                "<pre>string(6) &quot;string&quot;\n</pre>",
            ]
        );
    }

    public function testDebugDumpEcho()
    {
        Debug::setSapi('cli');
        $data = 'string';

        ob_start();
        $result1 = Debug::dump($data, null, true);
        $result2 = ob_get_contents();
        ob_end_clean();

        $this->assertContains('string(6) "string"', $result1);
        $this->assertEquals($result1, $result2);
    }

    public function testDebugDumpLabel()
    {
        Debug::setSapi('cli');
        $data = 'string';
        $label = 'LABEL';
        $result = Debug::dump($data, $label, false);
        $result = str_replace([PHP_EOL, "\n"], '_', $result);
        $expected = "_{$label} _string(6) \"string\"__";
        $this->assertEquals($expected, $result);
    }

    /**
     * @group ZF-4136
     * @group ZF-1663
     */
    public function testXdebugEnabledAndNonCliSapiDoesNotEscapeSpecialChars()
    {
        if (! extension_loaded('xdebug')) {
            $this->markTestSkipped('This test only works in combination with xdebug.');
        }

        Debug::setSapi('apache');
        $a = ['a' => 'b'];

        $result = Debug::dump($a, 'LABEL', false);
        $this->assertContains('<pre>', $result);
        $this->assertContains('</pre>', $result);
    }

    public function testDebugHaveEscaper()
    {
        Debug::setSapi('apache');

        $escaper = new Escaper;
        Debug::setEscaper($escaper);

        $a = ['a' => '<script type="text/javascript"'];
        $result = Debug::dump($a, 'LABEL', false);
        $this->assertContains('&lt;script type=&quot;text/javascript&quot;&quot;', $result);
    }
}
