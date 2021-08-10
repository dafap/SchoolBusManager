<?php
/**
 * Extension de la classe \TCPDF
 *
 * @project sbm
 * @package SbmPdf\Model\Tcpdf
 * @filesource Tcpdf.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 août 2021
 * @version 2021-2.6.3
 */
namespace SbmPdf\Model\Tcpdf;

use SbmPdf\Model\Document\PageHeaderInterface;
use SbmPdf\Model\Document\PageFooterInterface;
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
     * Objet PageHeader
     *
     * @var PageHeaderInterface
     */
    private $page_header;

    /**
     * Objet PageFooter
     *
     * @var PageFooterInterface
     */
    private $page_footer;

    /**
     * Méthode à appeler dans la méthode setTableHeader()
     *
     * @var callable
     */
    private $tableHeaderMethod;

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
        $this->page_header = null;
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
        $this->setPrintHeader($config->document->pageheader);
        $this->setPrintFooter($config->document->pagefooter);
        $this->setHeaderMargin(); // valeur par défaut par précaution
        return $this;
    }

    /**
     * Exécute les méthodes de l'objet TCPDF avec les paramètres indiqués.
     * La méthode est la clé, les paramètres sont les valeurs.
     * Si une méthode a plusieurs paramètres ils sont indiqués dans un tableau.
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $method => $args) {
            if (is_array($args)) {
                switch (count($args)) {
                    case 0:
                        $this->{$method}();
                        break;
                    case 1:
                        $this->{$method}($args[0]);
                        break;
                    case 2:
                        $this->{$method}($args[0], $args[1]);
                        break;
                    case 3:
                        $this->{$method}($args[0], $args[1], $args[2]);
                        break;
                    default:
                        break;
                }
            } else {
                $this->{$method}($args);
            }
        }
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
     * Enregistre l'objet décrivant l'entête de page.
     *
     * @param PageHeaderInterface $oPageHeader
     * @return self
     */
    public function setPageHeaderObject(PageHeaderInterface $oPageHeader = null)
    {
        $this->page_header = $oPageHeader;
        $this->setHeaderMargin($oPageHeader->getMarginTop());
        $this->setHeaderFont($oPageHeader->getFont());
        return $this;
    }

    public function setPageFooterObject(PageFooterInterface $oPageFooter = null)
    {
        $this->page_footer = $oPageFooter;
        $this->setFooterMargin($oPageFooter->getMargin());
        $this->setFooterFont($oPageFooter->getFont());
        $this->setFooterData($oPageFooter->getTextColorArray(),
            $oPageFooter->getLineColorArray());
        return $this;
    }

    /**
     * Enregistre la méthode qui dessine l'entête de tableau lors des changements de page
     *
     * @param callable $method
     * @return \SbmPdf\Model\Tcpdf\Tcpdf
     */
    public function setTableHeaderMethod(callable $method = null)
    {
        $this->tableHeaderMethod = $method;
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
    public function saveFontAndColors(): Parameters
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
    public function restoreFontAndColors(Parameters $params)
    {
        $this->SetFont($params->FontFamily, $params->FontStyle, $params->FontSizePt);
        $this->DrawColor = $params->DrawColor;
        $this->FillColor = $params->FillColor;
        $this->TextColor = $params->TextColor;
        $this->ColorFlag = $params->ColorFlag;
        $this->bgcolor = $params->bgcolor;
        $this->fgcolor = $params->fgcolor;
    }

    // =================== SURCHARGE DES METHODES DE \TCPDF ========================

    /**
     * Surcharge la méthode en insérant des actions entre endPage() et startPage()
     *
     * {@inheritdoc}
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
        // ++++++++++++++++ Ajout de la surcharge +++++++++++++++++++
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
        // détermine s'il faut changer l'entête de page
        if ($this->page_header->hasChanged()) {
            $this->resetHeaderTemplate();
        }
        // +++++++++++++++ Fin de la surcharge ++++++++++++++++
        // start new page
        $this->startPage($orientation, $format, $tocpage);
    }

    /**
     * AddPage() conditionnel.
     * La hauteur $h doit être disponible sinon on change de page.
     *
     * @param float $h
     */
    public function changePageIfNeed(float $h)
    {
        $this->checkPageBreak($h);
    }

    /**
     * Surcharge de la méthode.
     * Pour définir un nouveau modèle d'en-tête de page, il suffit de passer un objet
     * du namespace \SbmPdf\Model\Document\PageHeader implémentant
     * \SbmPdf\Model\Document\PageHeaderInterface.
     * La mise en place se fait par la méthode setPageHeaderObject().
     * Cette méthode est appelée par startPage().
     *
     * @see TCPDF::Header()
     */
    public function Header()
    {
        if ($this->header_xobjid === false) {
            if ($this->page_header instanceof PageHeaderInterface) {
                $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
                $this->page_header->render($this);
                $this->endTemplate();
            } else {
                return parent::Header();
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

    /**
     * Ligne d'entête d'un tableau (thead)
     * Surcharge la méthode de \TCPDF
     * Cette méthode est appelée dans startPage()
     *
     * {@inheritdoc}
     * @see TCPDF::setTableHeader()
     */
    protected function setTableHeader()
    {
        if (is_callable($callable = $this->tableHeaderMethod) &&
            $this->section_document == self::SECTION_DOCBODY) {
            $callable();
        }
    }

    public function ParentFooter()
    {
        parent::Footer();
    }

    public function Footer()
    {
        if ($this->page_footer instanceof PageFooterInterface) {
            $this->page_footer->render($this);
        } else {
            parent::Footer();
        }
    }
}