<?php
/**
 * @see       https://github.com/zendframework/zend-barcode for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-barcode/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Barcode\Object;

use Zend\Barcode;
use Zend\Barcode\Object\Exception\BarcodeValidationException;

/**
 * @group      Zend_Barcode
 */
class IdentcodeTest extends TestCommon
{
    protected function getBarcodeObject($options = null)
    {
        return new Barcode\Object\Identcode($options);
    }

    public function testType()
    {
        $this->assertSame('identcode', $this->object->getType());
    }

    public function checksum()
    {
        yield ['12345678901', 6];
        yield ['709003', 4];
        yield ['0709003', 4];
        yield ['00709003', 4];
        yield ['000709003', 4];
        yield ['0000709003', 4];
        yield ['00000709003', 4];
    }

    /**
     * @dataProvider checksum
     * @param string $text
     * @param int $checksum
     */
    public function testChecksum($text, $checksum)
    {
        $this->assertSame($checksum, $this->object->getChecksum($text));
    }

    public function testSetText()
    {
        $this->object->setText('00123456789');
        $this->assertSame('00123456789', $this->object->getRawText());
        $this->assertSame('001234567890', $this->object->getText());
        $this->assertSame('00.123 456.789 0', $this->object->getTextToDisplay());
    }

    public function testSetTextWithout13Characters()
    {
        $this->object->setText('123456789');
        $this->assertSame('123456789', $this->object->getRawText());
        $this->assertSame('001234567890', $this->object->getText());
        $this->assertSame('00.123 456.789 0', $this->object->getTextToDisplay());
    }

    public function testSetTextWithoutChecksumHasNoEffect()
    {
        $this->object->setText('00123456789');
        $this->object->setWithChecksum(false);
        $this->assertSame('00123456789', $this->object->getRawText());
        $this->assertSame('001234567890', $this->object->getText());
        $this->assertSame('00.123 456.789 0', $this->object->getTextToDisplay());
    }

    public function testSetTextWithSpaces()
    {
        $this->object->setText(' 00123456789 ');
        $this->assertSame('00123456789', $this->object->getRawText());
        $this->assertSame('001234567890', $this->object->getText());
        $this->assertSame('00.123 456.789 0', $this->object->getTextToDisplay());
    }

    public function testSetTextWithChecksumNotDisplayed()
    {
        $this->object->setText('00123456789');
        $this->object->setWithChecksumInText(false);
        $this->assertSame('00123456789', $this->object->getRawText());
        $this->assertSame('001234567890', $this->object->getText());
        $this->assertSame('00.123 456.789 0', $this->object->getTextToDisplay());
    }

    public function testBadTextDetectedIfChecksumWished()
    {
        $this->expectException('\Zend\Barcode\Object\Exception\ExceptionInterface');
        $this->object->setText('a');
        $this->object->setWithChecksum(true);
        $this->object->getText();
    }

    public function testCheckGoodParams()
    {
        $this->object->setText('00123456789');
        $this->assertTrue($this->object->checkParams());
    }


    public function testGetKnownWidthWithoutOrientation()
    {
        $this->object->setText('00123456789');
        $this->assertEquals(137, $this->object->getWidth());
        $this->object->setWithQuietZones(false);
        $this->assertEquals(117, $this->object->getWidth(true));
    }

    public function testCompleteGeneration()
    {
        $this->object->setText('00123456789');
        $this->object->draw();
        $instructions = $this->loadInstructionsFile('Identcode_00123456789_instructions');
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithBorder()
    {
        $this->object->setText('00123456789');
        $this->object->setWithBorder(true);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Identcode_00123456789_border_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithOrientation()
    {
        $this->object->setText('00123456789');
        $this->object->setOrientation(60);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Identcode_00123456789_oriented_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithBorderWithOrientation()
    {
        $this->object->setText('00123456789');
        $this->object->setOrientation(60);
        $this->object->setWithBorder(true);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Identcode_00123456789_border_oriented_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testGetDefaultHeight()
    {
        // Checksum activated => text needed
        $this->object->setText('00123456789');
        $this->assertEquals(62, $this->object->getHeight(true));
    }

    public function testChecksumIsNotProvided()
    {
        $this->object->setText('12345678901');
        self::assertSame('12.345 678.901 6', $this->object->getTextToDisplay());
    }

    public function testProvidedChecksum()
    {
        $this->object->setProvidedChecksum(true);
        $this->object->setText('123456789016');
        self::assertSame('12.345 678.901 6', $this->object->getTextToDisplay());
    }

    public function testProvidedChecksumInvalid()
    {
        $this->object->setProvidedChecksum(true);
        $this->object->setText('123456789012');

        $this->expectException(BarcodeValidationException::class);
        $this->expectExceptionMessage('The input failed checksum validation');
        $this->object->getTextToDisplay();
    }
}
