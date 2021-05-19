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
class RoyalmailTest extends TestCommon
{
    protected function getBarcodeObject($options = null)
    {
        return new Barcode\Object\Royalmail($options);
    }

    public function testType()
    {
        $this->assertSame('royalmail', $this->object->getType());
    }

    public function testChecksum()
    {
        $this->assertSame('W', $this->object->getChecksum('012345'));
        $this->assertSame('H', $this->object->getChecksum('06CIOU'));
        $this->assertSame('K', $this->object->getChecksum('SN34RD1A'));
    }

    public function testSetText()
    {
        $this->object->setText('012345');
        $this->assertSame('012345', $this->object->getRawText());
        $this->assertSame('012345W', $this->object->getText());
        $this->assertSame('012345W', $this->object->getTextToDisplay());
    }

    public function testSetTextWithoutChecksumHasNoEffect()
    {
        $this->object->setText('012345');
        $this->object->setWithChecksum(false);
        $this->assertSame('012345', $this->object->getRawText());
        $this->assertSame('012345W', $this->object->getText());
        $this->assertSame('012345W', $this->object->getTextToDisplay());
    }

    public function testSetTextWithSpaces()
    {
        $this->object->setText(' 012345 ');
        $this->assertSame('012345', $this->object->getRawText());
        $this->assertSame('012345W', $this->object->getText());
        $this->assertSame('012345W', $this->object->getTextToDisplay());
    }

    public function testSetTextWithChecksumNotDisplayed()
    {
        $this->object->setText('012345');
        $this->object->setWithChecksumInText(false);
        $this->assertSame('012345', $this->object->getRawText());
        $this->assertSame('012345W', $this->object->getText());
        $this->assertSame('012345W', $this->object->getTextToDisplay());
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
        $this->object->setText('012345');
        $this->assertTrue($this->object->checkParams());
    }

    public function testGetKnownWidthWithoutOrientation()
    {
        $this->object->setText('012345');
        $this->assertEquals(158, $this->object->getWidth());
        $this->object->setWithQuietZones(false);
        $this->assertEquals(118, $this->object->getWidth(true));
    }

    public function testCompleteGeneration()
    {
        $this->object->setText('012345');
        $this->object->draw();
        $instructions = $this->loadInstructionsFile('Royalmail_012345_instructions');
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithBorder()
    {
        $this->object->setText('012345');
        $this->object->setWithBorder(true);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Royalmail_012345_border_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithOrientation()
    {
        $this->object->setText('012345');
        $this->object->setOrientation(60);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Royalmail_012345_oriented_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testCompleteGenerationWithBorderWithOrientation()
    {
        $this->object->setText('012345');
        $this->object->setOrientation(60);
        $this->object->setWithBorder(true);
        $this->object->draw();
        $instructions = $this->loadInstructionsFile(
            'Royalmail_012345_border_oriented_instructions'
        );
        $this->assertEquals($instructions, $this->object->getInstructions());
    }

    public function testGetDefaultHeight()
    {
        // Checksum activated => text needed
        $this->object->setText('012345');
        $this->assertEquals(20, $this->object->getHeight(true));
    }

    public function testChecksumIsNotProvided()
    {
        $this->object->setText('123456');
        self::assertSame('1234562', $this->object->getTextToDisplay());
    }

    public function testProvidedChecksum()
    {
        $this->object->setProvidedChecksum(true);
        $this->object->setText('1234562');
        self::assertSame('1234562', $this->object->getTextToDisplay());
    }

    public function testProvidedChecksumInvalid()
    {
        $this->object->setProvidedChecksum(true);
        $this->object->setText('1234567');

        $this->expectException(BarcodeValidationException::class);
        $this->expectExceptionMessage('The input failed checksum validation');
        $this->object->getTextToDisplay();
    }
}
