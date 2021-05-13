<?php
/**
 * Template permettant de créer une entête de page.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document
 * @filesource LogoTitleString.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmPdf\Model\Document\PageHeader;

use SbmBase\Model\StdLib;
use SbmPdf\Model\Calculs;
use SbmPdf\Model\Document\PageHeaderInterface;
use SbmPdf\Model\Tcpdf\Tcpdf;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Parameters;

class LogoTitleString implements PageHeaderInterface
{
    use \SbmPdf\Model\Tcpdf\TcpdfTrait;

    /**
     * Référence aux données du document
     *
     * @var ArrayObject
     */
    private $data;

    private $visible;

    private $logo_visible;

    private $logo;

    private $logo_folder;

    private $logo_width;

    private $title_format;

    private $title;

    private $string_format;

    private $string;

    private $text_color;

    private $line_color;

    private $font_family;

    private $font_style;

    private $font_size;

    private $margin_top;

    private $image_blank;

    public function __construct(Parameters $params, Parameters $config, ArrayObject $data)
    {
        $this->data = $data;
        $this->visible = $params->get('pageheader',
            $config->document->get('pageheader', true));
        $this->logo_visible = $params->get('pageheader_logo_vsible',
            $config->document->get('pageheader_logo_visible', true));
        $this->logo_folder = StdLib::findParentPath(__DIR__,
            $config->document->get('url_path_images', K_PATH_IMAGES));
        $this->image_blank = $config->document->get('image_blank', K_BLANK_IMAGE);
        $this->logo = $params->get('pageheader_logo',
            $config->document->get('pageheader_logo', PDF_HEADER_LOGO));
        $this->logo_width = $params->get('pageheader_logo_width',
            $config->document->get('pageheader_logo_width', PDF_HEADER_LOGO_WIDTH));
        $this->title_format = $params->get('pageheader_title',
            $config->document->get('pageheader_title', 'Etat'));
        $this->string_format = $params->get('pageheader_string',
            $config->document->get('pageheader_string', 'éditée par School Bus Manager'));
        $this->text_color = $params->get('pageheader_text_color',
            $config->document->get('pageheader_text_color', 'black'));
        $this->line_color = $params->get('pageheader_line_color',
            $config->document->get('pageheader_line_color', 'black'));
        $this->font_family = $params->get('pageheader_font_family',
            $config->document->get('pageheader_font_family', PDF_FONT_NAME_MAIN));
        $this->font_style = $params->get('pageheader_font_style',
            $config->document->get('pageheader_font_style', ''));
        $this->font_size = $params->get('pageheader_font_size',
            $config->document->get('pageheader_font_size', PDF_FONT_SIZE_MAIN));
        $this->margin_top = $params->get('pageheader_margin',
            $config->document->get('pageheader_margin', PDF_MARGIN_HEADER));
        $this->hasChanged(); // initialise title et string
    }

    /**
     * Place le logo et renvoie la position Y du coin bas gauche du logo
     *
     * @param Tcpdf $pdf
     * @return float
     */
    protected function drawLogoAndGetY(Tcpdf $pdf): float
    {
        if ($this->isLogoVisible() && $this->logo && ($this->logo != $this->image_blank)) {
            if (substr($this->logo, 0, 2) == '//') {
                $filename = $this->logo; // url absolue
            } else {
                $filename = StdLib::concatPath($this->logo_folder, $this->logo); // url
                                                                                 // relative
            }
            if (file_exists($filename)) {
                $imgagetype = \TCPDF_IMAGES::getImageFileType($filename);
                if (($imgagetype == 'eps') or ($imgagetype == 'ai')) {
                    $pdf->ImageEps($filename, '', '', $this->logo_width);
                } elseif ($imgagetype == 'svg') {
                    $pdf->ImageSVG($filename, '', '', $this->logo_width);
                } else {
                    $pdf->Image($filename, '', '', $this->logo_width);
                }
                return $pdf->getImageRBY();
            }
        }
        return $pdf->GetY();
    }

    /**
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     *
     * @return bool
     */
    public function isLogoVisible(): bool
    {
        return $this->logo_visible;
    }

    /**
     *
     * @return string
     */
    public function getLogoFolder()
    {
        return $this->logo_folder ?: '';
    }

    /**
     *
     * @return mixed
     */
    public function getImageBlank()
    {
        return $this->image_blank;
    }

    /**
     *
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     *
     * @return mixed
     */
    public function getLogoWidth()
    {
        return $this->logo_width;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     *
     * @return mixed
     */
    public function getTextColor()
    {
        return $this->text_color;
    }

    /**
     *
     * @return mixed
     */
    public function getLineColor()
    {
        return $this->line_color;
    }

    /**
     *
     * @return mixed
     */
    public function getFontFamily()
    {
        return $this->font_family;
    }

    /**
     *
     * @return mixed
     */
    public function getFontStyle()
    {
        return $this->font_style;
    }

    /**
     *
     * @return mixed
     */
    public function getFontSize()
    {
        return $this->font_size;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\PageHeaderInterface::getFont()
     */
    public function getFont()
    {
        return [
            $this->getFontFamily(),
            $this->getFontStyle(),
            $this->getFontSize()
        ];
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\PageHeaderInterface::getMarginTop()
     */
    public function getMarginTop()
    {
        return $this->margin_top;
    }

    /**
     * Renvoie un booléen indiquant si le titre ou la chaine ont changé (en tenant compte
     * des calculs sur les données si nécessaire).
     * Met à jour les propriétés title et string si elles ont changé.
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\PageHeaderInterface::hasChanged()
     */
    public function hasChanged(): bool
    {
        $oCalculs = new Calculs($this->data);
        $title = $oCalculs->getResultat($this->title_format);
        $string = $oCalculs->getResultat($this->string_format);
        $change = false;
        if ($this->title != $title) {
            $change = true;
            $this->title = $title;
        }
        if ($this->string != $string) {
            $change = true;
            $this->string = $string;
        }
        return $change;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\PageHeaderInterface::render()
     */
    public function render(Tcpdf $pdf)
    {
        if (! $this->isVisible()) {
            return;
        }
        $originalMargins = $pdf->getOriginalMargins();
        $pdf->SetXY($originalMargins['left'], $this->margin_top, $pdf->getRTL());
        // logo s'il est visible
        $logo_y = $this->drawLogoAndGetY($pdf);
        $cell_height = $pdf->getCellHeight($this->font_size / $pdf->getScaleFactor());
        if ($pdf->getRTL()) {
            $header_x = $originalMargins['right'] + ($this->logo_width * 1.1);
        } else {
            $header_x = $originalMargins['left'] + ($this->logo_width * 1.1);
        }
        $cw = $pdf->getPageWidth() - $originalMargins['left'] - $originalMargins['right'] -
            ($this->logo_width * 1.1);
        // title
        $pdf->SetTextColorArray($this->convertColor($this->text_color));
        $pdf->SetFont($this->font_family, 'B', $this->font_size + 1);
        $pdf->SetX($header_x);
        $pdf->Cell($cw, $cell_height, $this->title, 0, 1);
        // string
        $pdf->SetFont($this->font_family, $this->font_style, $this->font_size);
        $pdf->SetX($header_x);
        $pdf->MultiCell($cw, $cell_height, $this->string, 0, '');
        // ligne horizontale
        $pdf->SetLineStyle(
            [
                'width' => 0.85 / $pdf->getScaleFactor(),
                'cap' => 'butt',
                'join' => 'miter',
                'dash' => 0,
                'color' => $this->convertColor($this->line_color)
            ]);
        $pdf->SetY((2.835 / $pdf->getScaleFactor()) + max($logo_y, $pdf->GetY()));
        if ($pdf->getRTL()) {
            $pdf->SetX($originalMargins['right']);
        } else {
            $pdf->SetX($originalMargins['left']);
        }
        $lw = $pdf->getPageWidth() - $originalMargins['left'] - $originalMargins['right'];
        $pdf->Cell($lw, 0, '', 'T', 0, 'C');
    }
}