<?php
/**
 * Modèle de pied de page par défaut
 *
 * Il y aura toujours à droite le "numéro de page / nombre de pages" (exitant dans le
 * modèle par défaut de tcpdf)
 * On peut rajouter une chaine à gauche et une chaine au centre du pied de page.
 * Les paramètres du modèle de pied de page se trouvent dans $this->config->document :
 * - 'pagefooter_string' :
 * la souscahine chaine à gauche : @gauche{chaine}
 * la souschaine au centre : @centre{chaine}
 * Tout ce qui ne sera pas dans l'accolade de l'une de ces 2 structures sera ignoré.
 * Les souschaines peuvent contenir les variables suivantes :
 * %date% : date courante
 * %nombre% : nombre de lignes de données dans cette page
 * %somme(colonne)% où colonne est le rang de la colonne surlaquelle porte la somme (à
 * partir de 1)
 * %max(colonne)%
 * %min(colonne)%
 * %moyenne(colonne)%
 * Toute autre chaine restera inchangée.
 * Attention, les valeurs non numériques de la colonne sont considérées comme 0 pour les
 * fonctions somme, max, min et moyenne.
 * - 'pagefooter_margin' : hauteur du pied de page dans l'unité utilisateur (mm)
 * - 'pagefooter_font_family' : string
 * - 'pagefooter_font_style' : string (voir tcpdf)
 * - 'pagefooter_font_size' : float
 * - 'pagefooter_text_color' : string couleur RGB
 * - 'pagefooter_line_color' : string couleur RGB
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document/PageFooter
 * @filesource PiedStandard.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmPdf\Model\Document\PageFooter;

use SbmPdf\Model\Document\Calculs;
use SbmPdf\Model\Document\PageFooterInterface;
use SbmPdf\Model\Element\ProcessFeatures;
use SbmPdf\Model\Tcpdf\Tcpdf;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Parameters;

class PiedStandard implements PageFooterInterface
{
    use \SbmPdf\Model\Tcpdf\TcpdfTrait;

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
     * @var bool
     */
    private $visible;

    /**
     *
     * @var string
     */
    private $string;

    /**
     *
     * @var float
     */
    private $margin;

    /**
     *
     * @var string
     */
    private $font_family;

    /**
     *
     * @var string
     */
    private $font_style;

    /**
     *
     * @var float
     */
    private $font_size;

    /**
     *
     * @var string
     */
    private $text_color;

    /**
     *
     * @var string
     */
    private $line_color;

    public function __construct(Parameters $params, Parameters $config,ArrayObject $data,
        ProcessFeatures $oProcess)
    {
        $this->data = $data;
        $this->oProcess = $oProcess;
        $this->visible = $params->get('pagefooter',
            $config->document->get('pagefooter', true));
        $this->string = $params->get('pagefooter_string',
            $config->document->get('pagefooter_string', ''));
        $this->margin = $params->get('pagefooter_margin',
            $config->document->get('pagefooter_margin', PDF_MARGIN_FOOTER));
        $this->font_family = $params->get('pagefooter_font_family',
            $config->document->get('pagefooter_font_family', PDF_FONT_NAME_DATA));
        $this->font_size = $params->get('pagefooter_font_style',
            $config->document->get('pagefooter_font_style', ''));
        $this->font_size = $params->get('pagefooter_font_size',
            $config->document->get('pagefooter_font_size', PDF_FONT_SIZE_DATA));
        $this->line_color = $params->get('pagefooter_text_color',
            $config->document->get('pagefooter_text_color', '#000000'));
        $this->line_color = $params->get('pagefooter_line_color',
            $config->document->get('pagefooter_line_color', '#000000'));
    }

    /**
     *
     * @return boolean
     */
    public function isVisible(): bool
    {
        return $this->visible;
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
     * @return number
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     *
     * @return string
     */
    public function getFontFamily()
    {
        return $this->font_family;
    }

    /**
     *
     * @return string
     */
    public function getFontStyle()
    {
        return $this->font_style;
    }

    /**
     *
     * @return number
     */
    public function getFontSize()
    {
        return $this->font_size;
    }

    /**
     *
     * @return array
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
     * @return string
     */
    public function getTextColor()
    {
        return $this->text_color;
    }

    /**
     *
     * @return string
     */
    public function getTextColorArray()
    {
        return $this->convertColor($this->text_color);
    }

    /**
     *
     * @return string
     */
    public function getLineColor()
    {
        return $this->line_color;
    }

    /**
     *
     * @return string
     */
    public function getLineColorArray()
    {
        return $this->convertColor($this->line_color);
    }

    public function render(Tcpdf $pdf)
    {
        if (! $this->isVisible()) {
            return;
        }
        $cur_y = $pdf->GetY();
        $pdf->ParentFooter();
        // remplacer les variables de la chaine
        $txt = $this->getString();
        if (! empty($txt) && $this->data->count()) {
            $oCalculs = new Calculs($this->data);
            $oCalculs->range($this->oProcess->getPointerPageBegin(),
                $this->oProcess->getPointerLast());
            $txt = $oCalculs->getResultat($txt);
            // découpe en 2 parties
            $matches = null;
            preg_match("/@gauche{([^}]*)}/", $txt, $matches);
            $part_gauche = isset($matches[1]) ? $matches[1] : '';
            preg_match("/@centre{([^}]*)}/", $txt, $matches);
            $part_centre = isset($matches[1]) ? $matches[1] : '';
            // écrire le résultat
            $pdf->SetY($cur_y);
            if (! empty($part_gauche)) {
                if ($part_gauche == strip_tags($part_gauche)) {
                    $pdf->Write(0, $part_gauche, '', false, 'L');
                } else {
                    $pdf->writeHTML($part_gauche, true, false, true, false, 'L');
                }
            }
            if (! empty($part_centre)) {
                $pdf->SetX(0);
                if ($part_centre == strip_tags($part_centre)) {
                    $pdf->Write(0, $part_centre, '', false, 'C');
                } else {
                    $pdf->writeHTML($part_centre, true, false, true, false, 'C');
                }
            }
        }
    }
}