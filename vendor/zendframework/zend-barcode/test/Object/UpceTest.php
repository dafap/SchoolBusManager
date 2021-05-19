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
class UpceTest extends TestCommon
{
    protected function getBarcodeObject($options = null)
    {
        return new Barcode\Object\Upce($options);
    }

    public function testType()
    {
        $this->assertSame('upce', $this->object->getType());
    }

    public function testChecksum()
    {
        $this->assertSame(9, $this->object->getChecksum('3456789'));
    }

    public function testSetText()
    {
        $this->object->setText('1234567');
        $this->assertSame('1234567', $this->object->getRawText());
        $this->assertSame('12345670', $this->object->getText());
        $this->assertSame('12345670', $this->object->getTextToDisplay());
    }

    public function testSetTextWithout8Characters()
    {
        $this->object->setText('12345');
        $this->assertSame('12345', $this->object->getRawText());
        $this->assertSame('00123457', $this->object->getText());
        $this->assertSame('00123457', $this->object->getTextToDisplay());
    }

    public function testSetTextWithout0or1AtBeginning()
    {
        $this->object->setText('3234567');
        $this->assertSame('3234567', $this->object->getRawText());
        $this->assertSame('02345673', $this->object->getText());
        $this->assertSame('02345673', $this->object->getTextToDisplay());
    }

    public function testSetTextWithoutChecksumHasNoEffect()
    {
        $this->object->setText('1234567');
        $this->object->setWithChecksum(false);
        $this->assertSame('1234567', $this->object->getRawText());
        $this->assertSame('12345670', $this->object->getText());
        $this->assertSame('12345670', $this->object->getTextToDisplay());
    }

    public function testSetTextWithSpaces()
    {
        $this->object->setText(' 1234567 ');
        $this->assertSame('1234567', $this->object->getRawText());
        $this->assertSame('12345670', $this->object->getText());
        $this->assertSame('12345670', $this->object->getTextToDisplay());
    }

    public function testSetTextWithChecksumNotDisplayed()
    {
        $this->object->setText('1234567');
        $this->object->setWithChecksumInText(false);
        $this->assertSame('1234567', $this->object->getRawText());
        $this->assertSame('12345670', $this->object->getText());
        $this->assertSame('12345670', $this->object->getTextToDisplay());
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
        $this->object->setText('1234567');
        $this->assertTrue($this->object->checkParams());
    }

    public function testGetKnownWidthWithoutOrientation()
    {
        $this->object->setText('1234567');
        $this->assertEquals(71, $this->object->getWidth());
        $this->object->setWithQuietZones(false);
        $this->assertEquals(71, $this->object->getWidth(true));
    }

    public function testCompleteGeneration()
    {
        $this->object->setText('1234567');
        $this->object->draw();
        $instructions = $this->loadInstructionsFile('Upce_1234567_instructions');
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithBorder()
    {
        $this->object->setText('1234567');
        $this->object->setWithBorder(true);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Upce_1234567_border_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithOrientation()
    {
        $this->object->setText('1234567');
        $this->object->setOrientation(60);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Upce_1234567_oriented_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithBorderWithOrientation()
    {
        $this->object->setText('1234567');
        $this->object->setOrientation(60);
        $this->object->setWithBorder(true);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Upce_1234567_border_oriented_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testGetDefaultHeight()
    {
        // Checksum activated => text needed
        $this->object->setText('1234567');
        $this->assertEquals(62, $this->object->getHeight(true));
    }

    public function testChecksumIsNotProvided()
    {
        $this->object->setText('1234567');
        self::assertSame('12345670', $this->object->getTextToDisplay());
    }

    public function testProvidedChecksum()
    {
        $this->object->setProvidedChecksum(true);
        $this->object->setText('12345670');
        self::assertSame('12345670', $this->object->getTextToDisplay());
    }

    public function testProvidedChecksumInvalid()
    {
        $this->object->setProvidedChecksum(true);
        $this->object->setText('12345678');

        $this->expectException(BarcodeValidationException::class);
        $this->expectExceptionMessage('The input failed checksum validation');
        $this->object->getTextToDisplay();
    }
}
