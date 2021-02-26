<?php
/**
 * Extension de la classe \TCPDF
 *
 * @project sbm
 * @package
 * @filesource Tcpdf.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 févr. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Tcpdf;

use TCPDF as VendorTcpdf;
use Zend\Stdlib\Parameters;

class Tcpdf extends VendorTcpdf
{

    const HORS_SECTION = 0;

    const SECTION_DOCHEADER = 1;

    const SECTION_DOCBODY = 2;

    const SECTION_DOCFOOTER = 3;

    /**
     *
     * @var Parameters
     */
    private $config;

    /**
     * Référence à une méthode publique de la classe appelante permettant de lancer une
     * action dans la classe appelante lors d'un AddPage
     *
     * @var callable
     */
    private $majIndex;

    /**
     *
     * @var int
     */
    private $last_pageNo;

    /**
     * Méthode à appeler dans la méthode Header()
     *
     * @var callable
     */
    private $page_header_method;

    /**
     * Permet de savoir dans quelle page on se situe (en-tête, corps, pied)
     *
     * @var int
     */
    private $section_document;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4',
        $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache,
            $pdfa);
        $this->last_pageNo = 0;
        $this->page_header_method = null;
    }

    /**
     * Renvoie la version courante de TCPDF
     *
     * @return string
     */
    public static function getVersion()
    {
        return \TCPDF_STATIC::getTCPDFVersion();
    }

    /**
     *
     * @return \Zend\Stdlib\Parameters
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *
     * @param \Zend\Stdlib\Parameters $config
     */
    public function setConfig(Parameters $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Affecte une fonction de mise à jour des index
     *
     * @param callable $majIndexPrevious
     * @return \SbmPdf\Model\Tcpdf\Tcpdf
     */
    public function setMajIndexAddPage($majIndexAddPage)
    {
        $this->majIndex = $majIndexAddPage;
        return $this;
    }

    /**
     *
     * @param int $section_document
     * @return self
     */
    public function setSectionDocument(int $section_document)
    {
        $this->section_document = $section_document;
        return $this;
    }

    /**
     * Enregistre la méthode à appeler dans la méthode Header()
     * Si le paramètre n'est pas callable on ne l'enregistre pas.
     *
     * @param callable $method
     * @return self
     */
    public function setPageHeaderMethod($method)
    {
        if (is_callable($method)) {
            $this->page_header_method = $method;
        } else {
            $this->page_header_method = null;
        }
        return $this;
    }

    /**
     * Sauvegarde le numéro de la page courante
     */
    public function savePageNo()
    {
        $this->last_pageNo = $this->pageNo();
        return $this;
    }

    /**
     * Renvoie les paramètres définissant la police courante et les couleurs actuelles
     *
     * @return \Zend\Stdlib\Parameters
     */
    public function saveFontColors(): Parameters
    {
        return new Parameters(
            [
                'FontFamily' => $this->FontFamily,
                'FontStyle' => $this->FontStyle,
                'FontSizePt' => $this->FontSizePt,
                'DrawColor' => $this->DrawColor,
                'FillColor' => $this->FillColor,
                'TextColor' => $this->TextColor,
                'ColorFlag' => $this->ColorFlag,
                'bgcolor' => $this->bgcolor,
                'fgcolor' => $this->fgcolor
            ]);
    }

    /**
     * Restaure la police et les couleurs
     *
     * @param \Zend\Stdlib\Parameters $params
     */
    public function restoreFontColors(Parameters $params)
    {
        $this->SetFont($params->FontFamily, $params->FontStyle, $params->FontSizePt);
        $this->DrawColor = $params->DrawColor;
        $this->FillColor = $params->FillColor;
        $this->TextColor = $params->TextColor;
        $this->ColorFlag = $params->ColorFlag;
        $this->bgcolor = $params->bgcolor;
        $this->fgcolor = $params->fgcolor;
    }

    /**
     * Surcharge de la méthode pour la gestion des sections du document (docheader,
     * docbody, docfooter) (non-PHPdoc)
     *
     * @see TCPDF::AddPage()
     */
    public function AddPage($orientation = '', $format = '', $keepmargins = false,
        $tocpage = false)
    {
        if ($this->inxobj) {
            // we are inside an XObject template
            return;
        }
        if (! isset($this->original_lMargin) or $keepmargins) {
            $this->original_lMargin = $this->lMargin;
        }
        if (! isset($this->original_rMargin) or $keepmargins) {
            $this->original_rMargin = $this->rMargin;
        }
        // terminate previous page
        $this->endPage();

        // détermine s'il faut changer de pied de page
        if ($this->last_pageNo && $this->section_document == self::SECTION_DOCBODY &&
            $this->PageNo() == $this->last_pageNo) {
            $this->last_pageNo = 0;
            $this->setPrintFooter($this->config->document->get('pagefooter', true));
        }

        // met à jour les index pour les calculs par page
        if (is_callable($callable = $this->majIndex)) {
            $callable();
        }

        // start new page
        $this->startPage($orientation, $format, $tocpage);
    }

    /**
     * Surcharge de la méthode.
     * Pour définir un nouveau modèle d'en-tête, il suffit d'écrire la méthode
     * templateHeader() dans la classe du template et de la mettre en place par
     * setTemplateHeader().
     *
     * @see TCPDF::Header()
     */
    public function Header()
    {
        if ($this->header_xobjid === false) {
            if (is_callable($this->page_header_method)) {
                $this->page_header_method();
            }
        }
        // print header template
        $x = 0;
        $dx = 0;
        if (! $this->header_xobj_autoreset and $this->booklet and (($this->page % 2) == 0)) {
            // adjust margins for booklet mode
            $dx = ($this->original_lMargin - $this->original_rMargin);
        }
        if ($this->rtl) {
            $x = $this->w + $dx;
        } else {
            $x = 0 + $dx;
        }
        $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
        if ($this->header_xobj_autoreset) {
            // reset header xobject template at each page
            $this->header_xobjid = false;
        }
    }
}