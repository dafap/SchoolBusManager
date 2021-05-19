<?php
/**
 * @see       https://github.com/zendframework/zend-barcode for the canonical source repository
 * @copyright Copyright (c) 2005-2019 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-barcode/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Barcode\Renderer;

use ZendPdf as Pdf;
use Zend\Barcode;
use Zend\Barcode\Object\Code39;

/**
 * @group      Zend_Barcode
 */
class PdfTest extends TestCommon
{
    public function setUp()
    {
        if (! getenv('TESTS_ZEND_BARCODE_PDF_SUPPORT')) {
            $this->markTestSkipped('Enable TESTS_ZEND_BARCODE_PDF_SUPPORT to test PDF render');
        }
        parent::setUp();
    }

    protected function getRendererObject($options = null)
    {
        return new \Zend\Barcode\Renderer\Pdf($options);
    }

    public function testType()
    {
        $this->assertSame('pdf', $this->renderer->getType());
    }

    public function testGoodPdfResource()
    {
        $pdfResource = new Pdf\PdfDocument();
        $this->renderer->setResource($pdfResource, 10);
    }

    public function testDrawReturnResource()
    {
        Barcode\Barcode::setBarcodeFont(__DIR__ . '/../Object/_fonts/Vera.ttf');
        $barcode = new Code39(['text' => '0123456789']);
        $this->renderer->setBarcode($barcode);
        $resource = $this->renderer->draw();
        $this->assertInstanceOf('ZendPdf\PdfDocument', $resource);
        Barcode\Barcode::setBarcodeFont('');
    }

    public function testDrawWithExistantResourceReturnResource()
    {
        Barcode\Barcode::setBarcodeFont(__DIR__ . '/../Object/_fonts/Vera.ttf');
        $barcode = new Code39(['text' => '0123456789']);
        $this->renderer->setBarcode($barcode);
        $pdfResource = new Pdf\PdfDocument();
        $this->renderer->setResource($pdfResource);
        $resource = $this->renderer->draw();
        $this->assertInstanceOf('ZendPdf\PdfDocument', $resource);
        $this->assertSame($resource, $pdfResource);
        Barcode\Barcode::setBarcodeFont('');
    }

    protected function getRendererWithWidth500AndHeight300()
    {
        $pdf = new Pdf\PdfDocument();
        $pdf->pages[] = new Pdf\Page('500:300:');
        return $this->renderer->setResource($pdf);
    }

    public function testHorizontalPositionToCenter()
    {
        $renderer = $this->getRendererWithWidth500AndHeight300();
        $barcode = new Code39(['text' => '0123456789']);
        $this->assertEquals(211, $barcode->getWidth());
        $renderer->setBarcode($barcode);
        $renderer->setHorizontalPosition('center');
        $renderer->draw();
        $this->assertEquals(197, $renderer->getLeftOffset());
    }

    public function testHorizontalPositionToRight()
    {
        $renderer = $this->getRendererWithWidth500AndHeight300();
        $barcode = new Code39(['text' => '0123456789']);
        $this->assertEquals(211, $barcode->getWidth());
        $renderer->setBarcode($barcode);
        $renderer->setHorizontalPosition('right');
        $renderer->draw();
        $this->assertEquals(394.5, $renderer->getLeftOffset());
    }

    public function testVerticalPositionToMiddle()
    {
        $renderer = $this->getRendererWithWidth500AndHeight300();
        $barcode = new Code39(['text' => '0123456789']);
        $this->assertEquals(62, $barcode->getHeight());
        $renderer->setBarcode($barcode);
        $renderer->setVerticalPosition('middle');
        $renderer->draw();
        $this->assertEquals(134, $renderer->getTopOffset());
    }

    public function testVerticalPositionToBottom()
    {
        $renderer = $this->getRendererWithWidth500AndHeight300();
        $barcode = new Code39(['text' => '0123456789']);
        $this->assertEquals(62, $barcode->getHeight());
        $renderer->setBarcode($barcode);
        $renderer->setVerticalPosition('bottom');
        $renderer->draw();
        $this->assertEquals(269, $renderer->getTopOffset());
    }
}
