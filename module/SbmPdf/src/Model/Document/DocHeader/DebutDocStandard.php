<?php
/**
 * Entête de document par défaut
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document/DocHeader
 * @filesource DebutDocStandard.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2021
 * @version 2021-2.6.3
 */
namespace SbmPdf\Model\Document\DocHeader;

use SbmPdf\Model\Tcpdf\Tcpdf;
use Zend\Stdlib\Parameters;

class DebutDocStandard
{
    use \SbmPdf\Model\Tcpdf\TcpdfTrait;

    const TITLE_DEFAULT = '';

    const MARGIN_DEFAULT = 20;

    /**
     *
     * @var bool
     */
    private $visible;

    /**
     *
     * @var bool
     */
    private $page_distincte;

    /**
     *
     * @var float
     */
    private $margin;

    /**
     *
     * @var bool
     */
    private $pageheader;

    /**
     *
     * @var bool
     */
    private $pagefooter;

    /**
     *
     * @var string
     */
    private $author;

    /**
     *
     * @var string
     */
    private $creator;

    /**
     *
     * @var string
     */
    private $keywords;

    /**
     *
     * @var string
     */
    private $subject;

    /**
     *
     * @var string
     */
    private $title;

    /**
     *
     * @var string
     */
    private $title_font_family;

    /**
     *
     * @var string
     */
    private $title_font_style;

    /**
     *
     * @var float
     */
    private $title_font_size;

    /**
     *
     * @var array
     */
    private $title_text_color;

    /**
     *
     * @var bool
     */
    private $title_line;

    /**
     *
     * @var array
     */
    private $title_line_color;

    /**
     *
     * @var string
     */
    private $subtitle;

    /**
     *
     * @var string
     */
    private $subtitle_font_family;

    /**
     *
     * @var string
     */
    private $subtitle_font_style;

    /**
     *
     * @var float
     */
    private $subtitle_font_size;

    /**
     *
     * @var array
     */
    private $subtitle_text_color;

    /**
     *
     * @var bool
     */
    private $subtitle_line;

    /**
     *
     * @var array
     */
    private $subtitle_line_color;

    public function __construct(Parameters $params, Parameters $config)
    {
        $this->visible = $params->get('docheader',
            $config->document->get('docheader', false));
        $this->page_distincte = $params->get('docheader_page_distincte',
            $config->document->get('docheader_page_distincte', true));
        $this->title = $params->get('title',
            $config->document->get('title', self::TITLE_DEFAULT));
        $this->subtitle = trim(
            $params->get('docheader_subtitle',
                $config->document->get('docheader_subtitle', '')), "\n");
        $this->margin = $params->get('docheader_margin',
            $config->document->get('docheader_margin', self::MARGIN_DEFAULT));
        $this->pageheader = $params->get('docheader_pageheader',
            $config->document->get('docheader_pageheader', false));
        $this->pagefooter = $params->get('docheader_footer',
            $config->document->get('docheader_footer', false));
        $this->subject = $params->get('subject', $config->document->get('subject', ''));
        $this->keywords = $params->get('keywords', $config->document->get('keywords', ''));
        $this->author = $params->get('author', $config->document->get('author', ''));
        $this->creator = $params->get('creator', $config->document->get('creator', ''));
        $this->title_font_family = $params->get('titre1_font_family',
            $config->document->get('titre1_font_family', PDF_FONT_NAME_MAIN));
        $this->title_font_style = $params->get('titre1_font_style',
            $config->document->get('titre1_font_style', 'BI'));
        $this->title_font_size = $params->get('titre1_font_size',
            $config->document->get('titre1_font_size', PDF_FONT_SIZE_MAIN));
        $this->title_line = $params->get('titre1_line',
            $config->document->get('titre1_line', false));
        $this->title_line_color = $this->convertColor(
            $params->get('titre1_line_color',
                $config->document->get('titre1_line_color', '#000000')));
        $this->title_text_color = $this->convertColor(
            $params->get('titre1_text_color',
                $config->document->get('titre1_text_color', '#000000')));
    }

    public static function description()
    {
        return new Parameters(
            [
                'libelle' => 'Tableau simple',
                'description' => <<<EOT
                Ce modèle produit un tableau où :<ul>
                <li>chaque cellule ne peut contenir qu'une seule ligne de texte.</li>
                <li>les retours à la ligne dans les données ne sont pas interprétés.</li>
                <li>ce modèle ne permet pas la fusion de cellules.</li></ul>
                EOT
            ]);
    }

    public function render(Tcpdf $pdf)
    {
        if (! $this->visible) {
            return;
        }
        $fontAndColors = $pdf->saveFontAndColors();
        $pdf->SetY($pdf->GetY() + $this->margin);
        if (! empty($this->title)) {
            $this->printPart($pdf, 'title');
            $pdf->SetY($pdf->GetY() + 4 * $pdf->getFontSize());
            $pdf->restoreFontAndColors($fontAndColors);
        }
        if (! empty($this->subtitle)) {
            $this->printPart($pdf, 'subtitle');
        }
        // ajouter ici les autres éléments de la page
        // fin de l'entête du document
        if (! $this->page_distincte) {
            // si page continue, tirer un trait
            $pdf->SetY($pdf->GetY() + 2 * $pdf->getFontSize());
            $pdf->Cell(0, 0, '', 'T', 1);
        }
        $pdf->restoreFontAndColors($fontAndColors);
    }

    protected function isHtml(string $str)
    {
        return strip_tags($str) != $str;
    }

    protected function printPart(Tcpdf $pdf, string $part)
    {
        switch ($part) {
            case 'title':
                $pdf->SetFont($this->title_font_family, $this->title_font_style,
                    $this->title_font_size);
                $pdf->SetTextColorArray($this->title_text_color);
                $pdf->SetDrawColorArray($this->title_line_color);
                if ($this->title_line) {
                    $pdf->SetLineStyle([
                        'color' => $this->title_line_color
                    ]);
                    $border = 1;
                } else {
                    $border = 0;
                }
                $pdf->Cell(0, $this->title_font_size * 1.2, $this->title, $border, 1, 'C');
                break;
            case 'subtitle':
                $pdf->SetFont($this->subtitle_font_family, $this->subtitle_font_style,
                    $this->subtitle_font_size);
                $pdf->SetTextColorArray($this->subtitle_text_color);
                $pdf->SetDrawColorArray($this->subtitle_line_color);
                if ($this->subtitle_line) {
                    $pdf->SetLineStyle([
                        'color' => $this->subtitle_line_color
                    ]);
                    $border = 1;
                } else {
                    $border = 0;
                }
                if ($this->isHtml($this->subtitle)) {
                    $pdf->writeHTML($this->subtitle, true, false, true, false, '');
                } else {
                    $pdf->SetFont('', 'I');
                    $pdf->Write(0, $this->subtitle, '', false, 'C');
                }
                break;
            case 'subject':
                if ($this->isHtml($this->subject)) {
                    $pdf->writeHTML($this->subject, true, false, true, false, '');
                } else {
                    $pdf->Write(0, $this->subject, '', false, 'C');
                }
                break;
            case 'author':
            case 'creator':
            case 'keywords':
                $pdf->Write(0, $this->{$part}, '', false, 'C');
                break;
            default:
                break;
        }
    }
}