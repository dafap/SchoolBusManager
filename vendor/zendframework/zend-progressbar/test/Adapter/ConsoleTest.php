<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\ProgressBar\Adapter;

use PHPUnit\Framework\TestCase;
use Zend\ProgressBar\Adapter;
use Zend\Stdlib\StringUtils;

/**
 * @group      Zend_ProgressBar
 */
class ConsoleTest extends TestCase
{
    protected function setUp()
    {
        stream_wrapper_register("zendprogressbaradapterconsole", MockupStream::class);
    }

    protected function tearDown()
    {
        stream_wrapper_unregister('zendprogressbaradapterconsole');
    }

    public function testWindowsWidth()
    {
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $adapter = new ConsoleStub();
            $adapter->notify(0, 100, 0, 0, null, null);
            $this->assertEquals(79, strlen($adapter->getLastOutput()));
        } else {
            $this->markTestSkipped('Not testable on non-windows systems');
        }
    }

    public function testStandardOutputStream()
    {
        $adapter = new ConsoleStub();

        $this->assertInternalType('resource', $adapter->getOutputStream());

        $metaData = stream_get_meta_data($adapter->getOutputStream());
        $this->assertEquals('php://stdout', $metaData['uri']);
    }

    public function testManualStandardOutputStream()
    {
        $adapter = new ConsoleStub(['outputStream' => 'php://stdout']);

        $this->assertInternalType('resource', $adapter->getOutputStream());

        $metaData = stream_get_meta_data($adapter->getOutputStream());
        $this->assertEquals('php://stdout', $metaData['uri']);
    }

    public function testManualErrorOutputStream()
    {
        $adapter = new ConsoleStub(['outputStream' => 'php://stderr']);

        $this->assertInternalType('resource', $adapter->getOutputStream());

        $metaData = stream_get_meta_data($adapter->getOutputStream());
        $this->assertEquals('php://stderr', $metaData['uri']);
    }

    public function testFixedWidth()
    {
        $adapter = new ConsoleStub(['width' => 30]);
        $adapter->notify(0, 100, 0, 0, null, null);

        $this->assertEquals('  0% [----------]             ', $adapter->getLastOutput());
    }

    public function testInvalidElement()
    {
        $this->expectException(Adapter\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid element found');
        $adapter = new ConsoleStub(['width' => 30, 'elements' => ['foo']]);
    }

    public function testCariageReturn()
    {
        $adapter = new ConsoleStub(['width' => 30]);
        $adapter->notify(0, 100, 0, 0, null, null);
        $adapter->notify(0, 100, 0, 0, null, null);

        $this->assertEquals(str_repeat("\x08", 30) . '  0% [----------]             ', $adapter->getLastOutput());
    }

    public function testBarLayout()
    {
        $adapter = new ConsoleStub(['width' => 30]);
        $adapter->notify(50, 100, .5, 0, null, null);

        $this->assertContains(' 50% [#####-----]', $adapter->getLastOutput());
    }

    public function testBarOnly()
    {
        $adapter = new ConsoleStub(['width' => 20, 'elements' => [Adapter\Console::ELEMENT_BAR]]);
        $adapter->notify(0, 100, 0, 0, null, null);

        $this->assertEquals('[------------------]', $adapter->getLastOutput());
    }

    public function testPercentageOnly()
    {
        $adapter = new ConsoleStub(['width' => 20, 'elements' => [Adapter\Console::ELEMENT_PERCENT]]);
        $adapter->notify(0, 100, 0, 0, null, null);

        $this->assertEquals('  0%', $adapter->getLastOutput());
    }

    public function testEtaOnly()
    {
        $adapter = new ConsoleStub(['width' => 20, 'elements' => [Adapter\Console::ELEMENT_ETA]]);
        $adapter->notify(0, 100, 0, 0, null, null);

        $this->assertEquals('            ', $adapter->getLastOutput());
    }

    public function testCustomOrder()
    {
        $adapter = new ConsoleStub(['width' => 25, 'elements' => [Adapter\Console::ELEMENT_ETA,
                                                                      Adapter\Console::ELEMENT_PERCENT,
                                                                      Adapter\Console::ELEMENT_BAR]]);
        $adapter->notify(0, 100, 0, 0, null, null);

        $this->assertEquals('               0% [-----]', $adapter->getLastOutput());
    }

    public function testBarStyleIndicator()
    {
        $adapter = new ConsoleStub(
            ['width' => 20, 'elements' => [Adapter\Console::ELEMENT_BAR], 'barIndicatorChar' => '>']
        );
        $adapter->notify(10, 100, .1, 0, null, null);

        $this->assertContains('[##>---------------]', $adapter->getLastOutput());
    }

    public function testBarStyleIndicatorWide()
    {
        $adapter = new ConsoleStub(
            ['width' => 20, 'elements' => [Adapter\Console::ELEMENT_BAR], 'barIndicatorChar' => '[]']
        );
        $adapter->notify(10, 100, .1, 0, null, null);

        $this->assertContains('[##[]--------------]', $adapter->getLastOutput());
    }

    public function testBarStyleLeftRightNormal()
    {
        $adapter = new ConsoleStub(
            ['width' => 20, 'elements' => [Adapter\Console::ELEMENT_BAR], 'barLeftChar' => '+', 'barRightChar' => ' ']
        );
        $adapter->notify(10, 100, .1, 0, null, null);

        $this->assertContains('[++                ]', $adapter->getLastOutput());
    }

    public function testBarStyleLeftRightWide()
    {
        $adapter = new ConsoleStub(
            ['width' => 20, 'elements' => [Adapter\Console::ELEMENT_BAR], 'barLeftChar' => '+-', 'barRightChar' => '=-']
        );
        $adapter->notify(10, 100, .1, 0, null, null);

        $this->assertContains('[+-=-=-=-=-=-=-=-=-]', $adapter->getLastOutput());
    }

    public function testBarStyleLeftIndicatorRightWide()
    {
        $adapter = new ConsoleStub([
            'width' => 20,
            'elements' => [Adapter\Console::ELEMENT_BAR],
            'barLeftChar' => '+-',
            'barIndicatorChar' => '[]',
            'barRightChar' => '=-'
        ]);
        $adapter->notify(10, 100, .1, 0, null, null);

        $this->assertContains('[+-[]=-=-=-=-=-=-=-]', $adapter->getLastOutput());
    }

    public function testEtaDelayDisplay()
    {
        $adapter = new ConsoleStub(['width' => 100, 'elements' => [Adapter\Console::ELEMENT_ETA]]);

        $adapter->notify(33, 100, .33, 3, null, null);
        $this->assertContains('            ', $adapter->getLastOutput());

        $adapter->notify(66, 100, .66, 6, 3, null);
        $result = preg_match('#ETA 00:00:(\d)+#', $adapter->getLastOutput(), $match);

        $this->assertEquals(1, $result);
    }

    public function testEtaHighValue()
    {
        $adapter = new ConsoleStub(['width' => 100, 'elements' => [Adapter\Console::ELEMENT_ETA]]);

        $adapter->notify(1, 100005, .001, 5, 100000, null);

        $this->assertContains('ETA ??:??:??', $adapter->getLastOutput());
    }

    public function testTextElementDefaultLength()
    {
        $adapter = new ConsoleStub(
            ['width' => 100, 'elements' => [Adapter\Console::ELEMENT_TEXT, Adapter\Console::ELEMENT_BAR]]
        );
        $adapter->notify(0, 100, 0, 0, null, 'foobar');

        $this->assertContains('foobar               [', $adapter->getLastOutput());
    }

    public function testTextElementCustomLength()
    {
        $adapter = new ConsoleStub([
            'width' => 100,
            'elements' => [Adapter\Console::ELEMENT_TEXT, Adapter\Console::ELEMENT_BAR],
            'textWidth' => 10
        ]);
        $adapter->notify(0, 100, 0, 0, null, 'foobar');

        $this->assertContains('foobar     [', $adapter->getLastOutput());
    }

    public function testSetOutputStreamOpen()
    {
        $adapter = new Adapter\Console();
        $adapter->setOutputStream('zendprogressbaradapterconsole://test1');
        $this->assertArrayHasKey('test1', MockupStream::$tests);
    }

    public function testSetOutputStreamOpenFail()
    {
        $adapter = new Adapter\Console();

        $this->expectException(Adapter\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Unable to open stream');
        $adapter->setOutputStream(null);
    }

    public function testSetOutputStreamReplaceStream()
    {
        $adapter = new Adapter\Console();
        $adapter->setOutputStream('zendprogressbaradapterconsole://test2');
        $this->assertArrayHasKey('test2', MockupStream::$tests);
        $adapter->setOutputStream('zendprogressbaradapterconsole://test3');
        $this->assertArrayHasKey('test3', MockupStream::$tests);
        $this->assertArrayNotHasKey('test2', MockupStream::$tests);
    }

    public function testgetOutputStream()
    {
        $adapter = new Adapter\Console();
        $adapter->setOutputStream('zendprogressbaradapterconsole://test4');
        $resource = $adapter->getOutputStream();
        fwrite($resource, 'Hello Word!');
        $this->assertEquals('Hello Word!', MockupStream::$tests['test4']);
    }

    public function testgetOutputStreamReturnigStdout()
    {
        $adapter = new Adapter\Console();
        $resource = $adapter->getOutputStream();
        $this->assertInternalType('resource', $resource);
    }

    public function testFinishEol()
    {
        $adapter = new Adapter\Console();
        $adapter->setOutputStream('zendprogressbaradapterconsole://test5');
        $adapter->finish();
        $this->assertEquals(PHP_EOL, MockupStream::$tests['test5']);
    }

    public function testFinishNone()
    {
        $adapter = new Adapter\Console();
        $adapter->setOutputStream('zendprogressbaradapterconsole://test7');
        $adapter->setFinishAction(Adapter\Console::FINISH_ACTION_NONE);
        $adapter->finish();
        $this->assertEquals('', MockupStream::$tests['test7']);
    }

    public function testSetBarLeftChar()
    {
        $adapter = new Adapter\Console();

        $this->expectException(Adapter\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Character may not be empty');
        $adapter->setBarLeftChar(null);
    }

    public function testSetBarRightChar()
    {
        $adapter = new Adapter\Console();

        $this->expectException(Adapter\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Character may not be empty');
        $adapter->setBarRightChar(null);
    }

    public function testSetInvalidFinishAction()
    {
        $adapter = new Adapter\Console();

        $this->expectException(Adapter\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid finish action specified');
        $adapter->setFinishAction('CUSTOM_FINISH_ACTION');
    }

    /**
     * @group 6012
     */
    public function testMultibyteTruncateFixedWidth()
    {
        $outputWidth = 50;
        $adapter = new ConsoleStub(['width' => $outputWidth, 'elements' => [Adapter\Console::ELEMENT_PERCENT,
                                                                                      Adapter\Console::ELEMENT_BAR,
                                                                                      Adapter\Console::ELEMENT_ETA,
                                                                                      Adapter\Console::ELEMENT_TEXT]]);
        // @codingStandardsIgnoreStart
        $adapter->notify( 21, 100, .21, 60, 60, 'ChineseTest 這是多字節長度裁剪的測試。我們希望能有超過20名中國字符的長字符串');
        $this->assertEquals(' 21% [##-------] ETA 00:01:00 ChineseTest 這是多字節長度裁', $adapter->getLastOutput());
        // @codingStandardsIgnoreEnd

        $wrapper = StringUtils::getWrapper($adapter->getCharset());
        $this->assertEquals($outputWidth, $wrapper->strlen($adapter->getLastOutput()));
    }

    /**
     * @group 6012
     */
    public function testMultibytePadFixedWidth()
    {
        $outputWidth = 50;
        $adapter = new ConsoleStub(['width' => $outputWidth, 'elements' => [Adapter\Console::ELEMENT_PERCENT,
                                                                                      Adapter\Console::ELEMENT_BAR,
                                                                                      Adapter\Console::ELEMENT_ETA,
                                                                                      Adapter\Console::ELEMENT_TEXT]]);
        $adapter->notify(21, 100, .21, 60, 60, '這是');
        $this->assertEquals(' 21% [##-------] ETA 00:01:00 這是                  ', $adapter->getLastOutput());

        $wrapper = StringUtils::getWrapper($adapter->getCharset());
        $this->assertEquals($outputWidth, $wrapper->strlen($adapter->getLastOutput()));
    }
}
