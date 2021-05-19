<?php
/**
 * Fin de document par défaut
 *
 * Le title du document est (éventuellement, s'il n'y a pas de haut de page) placé en
 * style 'titre1'
 * Le docfooter_title de la page de fin du document (ou subtitle) est placé en style
 * 'titre2'
 * Le reste (subject et pagefooter_string) est en style 'main'
 * Les chaines docfooter_title, subject et pagefooter_string peuvent être codées en HTML.
 * La chaine string peut contenir des variables interprétées dans la classe Calculs.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document/DocFooter
 * @filesource FinDocStandard.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmPdf\Model\Document\DocFooter;

use SbmPdf\Model\Document\Calculs;
use SbmPdf\Model\Element\ProcessFeatures;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Parameters;
use SbmPdf\Model\Tcpdf\Tcpdf;

class FinDocStandard
{
    use \SbmPdf\Model\Tcpdf\TcpdfTrait;

    const TITLE_DEFAULT = '';

    const MARGIN_DEFAULT = 20;

    /**
     *
     * @var ArrayObject
     */
    private $data;

    /**
     *
     * @var ProcessFeatures
     */
    private $oProcess;

    /**
     *
     * @var Calculs
     */
    private $oCalculs;

    /**
     * correspond à la colonne docfooter de la table 'documents'
     *
     * @var bool
     */
    private $visible;

    /**
     * correspond à la colonne docfooter_page_distincte de la table 'documents'
     *
     * @var bool
     */
    private $page_distincte;

    /**
     * correspond à la colonne docfooter_insecable de la table 'documents'
     *
     * @var bool
     */
    private $insecable;

    /**
     * correspond à la colonne title de la table 'documents'
     *
     * @var string
     */
    private $title;

    /**
     * correspond à la colonne docfooter_title de la table 'documents'
     *
     * @var string
     */
    private $subtitle;

    /**
     * correspond à la colonne docfooter_string de la table 'documents'
     *
     * @var string
     */
    private $string;

    /**
     * correspond à la colonne docfooter_margin de la table 'documents'
     *
     * @var float
     */
    private $margin;

    /**
     * correspond à la colonne docfooter_pageheader de la table 'documents'
     *
     * @var bool
     */
    private $pageheader;

    /**
     * correspond à la colonne docfooter_pagefooter de la table 'documents'
     *
     * @var bool
     */
    private $pagefooter;

    /**
     *
     * @var string
     */
    private $subject;

    /**
     *
     * @var string
     */
    private $keywords;

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
     * à partir d'ici on paramètre avec les caractéristiques de titre1
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
     * à partir d'ici on paramètre avec les caractéristiques de titre2
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

    public function __construct(Parameters $params, Parameters $config, ArrayObject $data,
        ProcessFeatures $oProcess)
    {
        $this->data = $data;
        $this->oCalculs = new Calculs($data);
        $this->oProcess = $oProcess;
        $this->visible = $params->get('docfooter',
            $config->document->get('docfooter', true));
        $this->page_distincte = $params->get('docfooter_page_distincte',
            $config->document->get('docfooter_page_distincte', true));
        $this->insecable = $params->get('docfooter_insecable',
            $config->document->get('docfooter_insecable', true));
        $this->title = $params->get('title',
            $config->document->get('title', self::TITLE_DEFAULT));
        $this->subtitle = trim(
            $params->get('docfooter_title', $config->document->get('docfooter_title', '')),
            "\n");
        $this->string = trim(
            $params->get('docfooter_string',
                $config->document->get('docfooter_string', '')), "\n");
        $this->margin = $params->get('docfooter_margin',
            $config->document->get('docfooter_margin', self::MARGIN_DEFAULT));
        $this->pageheader = $params->get('docfooter_pageheader',
            $config->document->get('docfooter_pageheader', false));
        $this->pagefooter = $params->get('docfooter_footer',
            $config->document->get('docfooter_footer', false));
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
        $this->subtitle_font_family = $params->get('titre2_font_family',
            $config->document->get('titre2_font_family', PDF_FONT_NAME_MAIN));
        $this->subtitle_font_style = $params->get('titre2_font_style',
            $config->document->get('titre2_font_style', 'BI'));
        $this->subtitle_font_size = $params->get('titre2_font_size',
            $config->document->get('titre2_font_size', PDF_FONT_SIZE_MAIN));
        $this->subtitle_line = $params->get('titre2_line',
            $config->document->get('titre2_line', false));
        $this->subtitle_line_color = $this->convertColor(
            $params->get('titre2_line_color',
                $config->document->get('titre2_line_color', '#000000')));
        $this->subtitle_text_color = $this->convertColor(
            $params->get('titre2_text_color',
                $config->document->get('titre2_text_color', '#000000')));
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
        if ($this->insecable) {
            $this->changePageIfNeed($pdf);
        }
        $fontAndColors = $pdf->saveFontAndColors();
        $pdf->SetY($pdf->GetY() + $this->margin);
        if (! empty($this->title) && ! $this->pageheader) {
            $this->printPart($pdf, 'title');
            $pdf->Ln(4 * $pdf->getFontSize());
            $pdf->restoreFontAndColors($fontAndColors);
        }
        if (! empty($this->subtitle)) {
            $this->printPart($pdf, 'subtitle');
            $pdf->Ln(4 * $pdf->getFontSize());
            $pdf->restoreFontAndColors($fontAndColors);
        }

        if (! empty($this->subject)) {
            $this->printPart($pdf, 'subject');
            $pdf->Ln(2 * $pdf->getFontSize());
        }
        if (! empty($this->string)) {
            $this->printPart($pdf, 'string');
        }
        // ajouter ici les autres éléments de la page

        $pdf->restoreFontAndColors($fontAndColors);
    }

    protected function changePageIfNeed(Tcpdf $pdf)
    {
        $h = 0;
        if (! empty($this->title) && ! $this->pageheader) {
            $h += 5 * $this->title_font_size;
        }
        if (! empty($this->subtitle)) {
            $h += 5 * $this->subtitle_font_size;
        }
        if (! empty($this->subject)) {
            $pdf->setLastH($pdf->getCellHeight($pdf->getFontSize()));
            $h += $pdf->getStringHeight(0, $this->subject);
        }
        if (! empty($this->string)) {
            $pdf->setLastH($pdf->getCellHeight($pdf->getFontSize()));
            $h += $pdf->getStringHeight(0, $this->string);
        }
        $pdf->changePageIfNeed($h);
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
            case 'string':
            case 'subject':
                if ($part == 'string') {
                    $txt = $this->oCalculs->getResultat($this->string);
                } else {
                    $txt = $this->{$part};
                }
                if ($this->isHtml($txt)) {
                    $pdf->writeHTML($txt, true, false, true, false, '');
                } else {
                    $pdf->Write(0, $txt, '', false, 'C');
                }
                break;
            case 'author':
            case 'creator':
            case 'keywords':
                $pdf->Write(0, $this->{$part}, '', false, '');
                break;
            default:
                break;
        }
    }
}