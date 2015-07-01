<?php
/**
 * Extension de la classe Tcpdf
 * 
 * Les modèles d'en-tête de pages sont définis par les méthodes templateHeaderMethodx où x est un entier à partir de 1
 * Le constructeur reçoit un ServiceManager $sm et un tableau de paramètres $params
 *
 *
 * @project sbm
 * @package SbmPdf/Model
 * @filesource Tcpdf.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juil. 2015
 * @version 2015-2
 */
namespace SbmPdf\Model;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Tag\Cloud\Decorator\HtmlCloud;
use SbmCommun\Model\StdLib;
use Zend\Db\Sql\Where;
use Zend\Stdlib\ArrayObject;
use Zend\Captcha\Dumb;

class Tcpdf extends \TCPDF
{

    const HORS_SECTION = 0;

    const SECTION_DOCHEADER = 1;

    const SECTION_DOCBODY = 2;

    const SECTION_DOCFOOTER = 3;

    const SBM_DOCHEADER_DELTA_SMALL = 5;

    const SBM_DOCHEADER_DELTA_WIDE = 10;

    const SBM_DOCFOOTER_INSECABLE_DELTA_TITLE = 30;

    const SBM_DOCFOOTER_INSECABLE_DELTA_NBLIGNES = 3;

    const DEFAULT_SBM_DOCUMENT_TITLE = 'Liste';

    const DEFAULT_SBM_DOCHEADER_MARGIN = 20;

    const DEFAULT_SBM_DOCFOOTER_MARGIN = 20;

    /**
     * Tableau des paramètres passés par l'évènement
     * Les clés du tableau sont <ul>
     * <li>'documentId' de type integer</li>
     * <li>'where' de type Zend\Db\Sql\Where qui reprend la sélection obtenue par le formulaire de critères</li></ul>
     *
     * @var array
     */
    protected $params = array();

    /**
     * ServiceManager
     *
     * @var ServiceLocatorInterface
     */
    protected $sm;

    /**
     * Tableau des modèles de pages dont les clés sont (header, footer, page)
     *
     * @var array
     */
    private $templates = array(
        'header' => null,
        'footer' => null,
        'page' => null
    );

    /**
     * SECTION_DOCHEADER (en-tête de document) ; SECTION_DOCBODY (corps du document) ; SECTION_DOCFOOTER (pied de document)
     *
     * @var int
     */
    private $section_document;

    /**
     * Numéro de la dernière page de l'en-tête de document
     *
     * @var int
     */
    private $last_page_docheader;

    /**
     * Tableau de configuration du document
     * Les clés sont <ul>
     * <li>'document' de type SbmCommun\Model\Db\ObjectDaya\Sys\Document</li>
     * <li>'docfields' array de SbmCommun\Model\Db\ObjectData\Sys\DocField</li>
     * <li>'doctable' de type SbmCommun\Model\Db\ObjectData\Sys\DocTable</li> (optionnel)
     * <li>'doccells' array de SbmCommun\Model\Db\ObjectData\Sys\DocCells</li> (optionnel)
     *
     * @var array
     */
    private $config = array();

    /**
     * Buffer contenant les données à placer dans le document, initialisé par la méthode getData() si nécessaire
     *
     * @var array
     */
    private $data = array();

    public function __construct(ServiceLocatorInterface $sm = null, $params = array())
    {
        if (is_null($sm))
            return; // nécessaire pour le fonctionnement de la méthode SbmAdmin\Form\DocumentPdf::getTemplateList()
        
        $this->sm = $sm;
        $this->params = $params;
        
        $this->section_document = self::HORS_SECTION;
        $this->last_page_docheader = 0;
        
        // document pdf
        $this->initConfigDocument();
        parent::__construct($this->getConfig('document', 'page_orientation', PDF_PAGE_ORIENTATION), PDF_UNIT, $this->getConfig('document', 'page_format', PDF_PAGE_FORMAT), true, 'UTF-8', false, false);
        $this->SetCreator($this->getConfig('document', 'creator', PDF_CREATOR));
        $this->SetAuthor($this->getConfig('document', 'author', PDF_AUTHOR));
        $this->SetTitle($this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE));
        $this->SetSubject($this->getConfig('document', 'subject', ''));
        $this->SetKeywords($this->getConfig('document', 'keywords', 'SBM, TS'));
        
        // en-tête de page
        $this->setPageHeader($this->getConfig('document', 'pageheader', false));
        
        // pied de page
        $this->setPageFooter($this->getConfig('document', 'pagefooter', false));
        
        // page
        $this->SetMargins($this->getConfig('document', 'page_margin_left', PDF_MARGIN_LEFT), $this->getConfig('document', 'page_margin_top', PDF_MARGIN_TOP), $this->getConfig('document', 'page_margin_right', PDF_MARGIN_RIGHT));
        
        // set auto page breaks
        $this->SetAutoPageBreak(TRUE, $this->getConfig('document', 'page_margin_bottom', PDF_MARGIN_BOTTOM));
        
        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/' . PDF_LANG . '.php')) {
            require_once (dirname(__FILE__) . '/lang/' . PDF_LANG . '.php');
            $this->setLanguageArray($l);
        }
        
        // set default monospaced font
        $this->SetDefaultMonospacedFont($this->getConfig('document', 'default_font_monospaced', PDF_FONT_MONOSPACED));
        
        // set font
        $this->SetFont($this->getConfig('document', 'main_font_family', PDF_FONT_NAME_DATA), trim($this->getConfig('document', 'main_font_style', '')), $this->getConfig('document', 'main_font_size', PDF_FONT_SIZE_DATA));
    }

    /**
     * Renvoie le ServiceManager
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->sm;
    }

    /**
     * Renvoie $this->params[$key] si la clé existe ou $default sinon
     *
     * @param string $key
     *            clé de la valeur recherchée
     * @param mixed $default
     *            valeur par défaut, renvoyée si la clé n'existe pas
     *            
     * @return mixed valeur renvoyée
     */
    protected function getParam($key, $default)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        } else {
            return $default;
        }
    }

    /**
     * Renvoie $this->config[$section][$key] si les clés existent.
     *
     * @param string|array $sections
     *            document, doctable, docfields, doccells ou array(doctable, thead | tbody | tfoot)
     * @param string $key
     *            clé de la valeur recherchée
     * @param mixed $default
     *            valeur par défaut renvoyée si la clé n'existe pas
     *            
     * @throws Exception si la section n'est pas présente dans le tableau config ou cette section n'est pas un tableau
     *        
     * @return mixed valeur renvoyée
     */
    protected function getConfig($sections, $key, $default = null, $exception = false)
    {
        if (is_string($sections)) {
            $sections = array(
                $sections
            );
        }
        if (StdLib::array_keys_exists($sections, $this->config)) {
            $array = $this->config;
            reset($array);
            foreach ($sections as $k) {
                $array = $array[$k];
            }
            if (is_array($array)) {
                return array_key_exists($key, $array) ? $array[$key] : $default;
            }
        }
        if (! $exception) {
            return $default;
        }
        // on doit lancer une exception. Il faut savoir pourquoi.
        $config = 'config';
        $array = $this->config;
        foreach ($sections as $k) {
            if (! array_key_exists($k, $array)) {
                ob_start();
                var_dump($array);
                $dump_array = html_entity_decode(strip_tags(ob_get_clean()));
                $message = sprintf("La clé %s n'existe pas dans le tableau %s.\n%s", $k, $config, $dump_array);
                break;
            } elseif (! is_array($array[$k])) {
                ob_start();
                var_dump($array);
                $dump_array = html_entity_decode(strip_tags(ob_get_clean()));
                $message = sprintf("La clé %s existe dans le tableau %s mais ne donne pas un tableau.\n%s", $k, $config, $dump_array);
                break;
            } else {
                $array = $array[$k];
                $config .= '[' . $k . ']';
            }
        }
        
        throw new Exception($message);
    }

    /**
     * Lance la construction du document pdf
     */
    public function run()
    {
        // En-tête de document
        if ($this->getConfig('document', 'docheader', false)) {
            $this->sectionDocumentHeader();
        }
        
        // corps du document
        $this->sectionDocumentBody();
        
        // pied du document
        if ($this->getConfig('document', 'docfooter', false)) {
            $this->sectionDocumentFooter();
        }
        
        $this->Output($this->getConfig('document', 'out_name', 'doc.pdf'), $this->getConfig('document', 'out_mode', 'I'));
    }

    /**
     * En-tête de document
     */
    protected function sectionDocumentHeader()
    {
        $this->section_document = self::SECTION_DOCHEADER;
        $this->setPrintHeader($this->getConfig('document', 'docheader_pageheader', false));
        $this->setPrintFooter($this->getConfig('document', 'docheader_pagefooter', false));
        $this->AddPage();
        $templateDocHeaderMethod = 'templateDocHeaderMethod' . $this->getConfig('document', 'docheader_templateId', 1);
        if (method_exists($this, $templateDocHeaderMethod)) {
            $this->{$templateDocHeaderMethod}();
        } else {
            $this->templateDocHeaderMethod1(); // méthode par défaut
        }
        $this->last_page_docheader = $this->PageNo();
    }

    /**
     * Corps du document
     */
    protected function sectionDocumentBody()
    {
        $this->section_document = self::SECTION_DOCBODY;
        $current_font_family = $this->getFontFamily();
        $current_font_style = $this->getFontStyle();
        $current_font_size = $this->getFontSizePt();
        $current_text_color = $this->TextColor;
        $current_fill_color = $this->FillColor;
        $current_draw_color = $this->DrawColor;
        $current_color_flag = $this->ColorFlag;
        $current_fgcolor = $this->fgcolor;
        $current_bgcolor = $this->bgcolor;
        // gestion du header, du footer et du AddPage
        if ($this->getConfig('document', 'docheader', false)) {
            // il y avait une page d'en-tête
            if ($this->getConfig('document', 'pageheader', false) != $this->getConfig('document', 'docheader_pageheader', false)) {
                // configuration différente de l'en-tête de page : on la chage
                $this->resetHeaderTemplate();
                $this->setPrintHeader($this->getConfig('document', 'pageheader', false));
            }
            if ($this->getConfig('document', 'docheader_page_distincte', false)) {
                // page distincte
                $this->AddPage();
            }
        } else {
            $this->setPrintHeader($this->getConfig('document', 'pageheader', false));
            $this->setPrintFooter($this->getConfig('document', 'pagefooter', false));
            $this->AddPage();
        }
        $templateDocBodyMethod = 'templateDocBodyMethod' . $this->getConfig('document', 'page_templateId', 1);
        if (method_exists($this, $templateDocBodyMethod)) {
            $this->{$templateDocBodyMethod}();
        } else {
            $this->templateDocBodyMethod1();
        }
        $this->SetFont($current_font_family, $current_font_style, $current_font_size);
        $this->DrawColor = $current_draw_color;
        $this->FillColor = $current_fill_color;
        $this->TextColor = $current_text_color;
        $this->ColorFlag = $current_color_flag;
        $this->bgcolor = $current_bgcolor;
        $this->fgcolor = $current_fgcolor;
    }

    /**
     * Si le pied de document n'est pas dans une page distincte, l'en-tête de page reste celui de la section docbody (car la page est déjà commencée)
     * sinon, on configure l'en-tête de page de cette section
     *
     * Pour le pied de page, on configure toujours celui qui est prévu dans la section docfooter
     */
    protected function sectionDocumentFooter()
    {
        $this->section_document = self::SECTION_DOCFOOTER;
        // page distincte pour le pied de document
        if ($this->getConfig('document', 'docfooter_page_distincte', false)) {
            if ($this->getConfig('document', 'pageheader', false) != $this->getConfig('document', 'docfooter_pageheader', false)) {
                $this->resetHeaderTemplate();
                $this->setPrintHeader($this->getConfig('document', 'docfooter_pageheader', false));
            }
            $this->AddPage();
        }
        // dans tous les cas, on place le pied de page correctement
        $this->setPrintFooter($this->getConfig('document', 'docfooter_pagefooter', false));
        
        $templateDocFooterMethod = 'templateDocFooterMethod' . $this->getConfig('document', 'docfooter_templateId', 1);
        if (method_exists($this, $templateDocFooterMethod)) {
            $this->{$templateDocFooterMethod}();
        } else {
            $this->templateDocFooterMethod1(); // méthode par défaut
        }
    }

    /**
     * En-tête de page
     *
     * @param bool $has_pageheader            
     */
    protected function setPageHeader($has_pageheader)
    {
        $this->setPrintHeader($has_pageheader);
        $this->SetHeaderData($this->getConfig('document', 'pageheader_logo', PDF_HEADER_LOGO), $this->getConfig('document', 'pageheader_logo_width', PDF_HEADER_LOGO_WIDTH), $this->getConfig('document', 'pageheader_title', 'Etat'), $this->getConfig('document', 'pageheader_string', 'éditée par School Bus Manager'), $this->convertColor($this->getConfig('document', 'pageheader_text_color', '000000')), $this->convertColor($this->getConfig('document', 'pageheader_line_color', '000000')));
        $this->setHeaderFont(Array(
            $this->getConfig('document', 'pageheader_font_family', PDF_FONT_NAME_MAIN),
            trim($this->getConfig('document', 'pageheader_font_style', '')),
            $this->getConfig('document', 'pageheader_font_size', PDF_FONT_SIZE_MAIN)
        ));
        $this->SetHeaderMargin($this->getConfig('document', 'pageheader_margin', PDF_MARGIN_HEADER));
    }

    /**
     * Pied de page
     *
     * @param bool $has_pagefooter            
     */
    protected function setPageFooter($has_pagefooter)
    {
        $this->setPrintFooter($has_pagefooter);
        $this->setFooterData($this->convertColor($this->getConfig('document', 'pagefooter_text_color', '000000')), $this->convertColor($this->getConfig('document', 'pagefooter_line_color', '000000')));
        $this->setFooterFont(Array(
            $this->getConfig('document', 'pagefooter_font', PDF_FONT_NAME_DATA),
            trim($this->getConfig('document', 'pagefooter_font_style', '')),
            $this->getConfig('document', 'pagefooter_font_size', PDF_FONT_SIZE_DATA)
        ));
        $this->setFooterMargin($this->getConfig('document', 'pagefooter_margin', PDF_MARGIN_FOOTER));
    }

    protected function getDocumentId()
    {
        return $this->getParam('documentId', 1);
    }

    protected function getWhere()
    {
        $where = $this->getParam('where', new Where());
        $filter = $this->getConfig('document', 'filter', null);
        if (! empty($filter)) {
            $where->literal($filter);
        }
        return $where;
    }

    protected function getOrderBy()
    {
        $orderBy = $this->getConfig('document', 'orderBy', null);
        return empty($orderBy) ? $this->getParam('orderBy', null) : $orderBy;
    }

    protected function getRecordSourceType()
    {
        if ($this->getParam('documentId', false) !== false) {
            return $this->getConfig('document', 'recordSourceType', 'T');
        } elseif ($this->getParam('recordSource', false) !== false) {
            return 'T';
        } else {
            throw new Exception('La clé `recordSource` du document par défaut n\'est pas définie dans la propriété `params`. Revoir l\'appel de l\'event.');
        }
    }

    /**
     * Renvoie un AbstractSbmTable sur la table indiquée dans la clé recordSource du document
     * ou, si elle ne convient pas, dans la clé recordSource des paramètres reçus.
     *
     * @throws Exception si aucune des clés ne donne un recordSource valide pour le ServiceManager
     *         (vérifier éventellement les enregistrements des clés dans module.config.php)
     *        
     * @return AbstractSbmTable
     */
    protected function getRecordSourceTable()
    {
        if ($this->getServiceLocator()->has($this->getConfig('document', 'recordSource', ''))) {
            return $this->getServiceLocator()->get($this->getConfig('document', 'recordSource', ''));
        } elseif ($this->getServiceLocator()->has($this->getParam('recordSource', ''))) {
            return $this->getServiceLocator()->get($this->getParam('recordSource', ''));
        } else {
            $config_source = $this->getConfig('document', 'recordSource', 'absente');
            $param_source = $this->getParam('recordSource', 'absente');
            throw new Exception(sprintf("La clé `recordSource` est invalide ou n'est pas précisée pour ce document.\nClé dans la configuration du document : %s\nClé dans les paramètres d'appel: %s", $config_source, $param_source));
        }
    }

    protected function initConfigDocument()
    {
        $table_documents = $this->getServiceLocator()->get('Sbm\Db\System\Documents');
        try {
            $this->config['document'] = $table_documents->getConfig($this->getDocumentId());
        } catch (\Exception $e) {
            $this->config['document'] = require (__DIR__ . '/default/documents.inc.php');
        }
    }

    /**
     * Surcharge de la méthode pour la gestion des sections du document (docheader, docbody, docfooter)
     *
     * (non-PHPdoc)
     *
     * @see TCPDF::AddPage()
     */
    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
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
        if ($this->last_page_docheader && $this->section_document == self::SECTION_DOCBODY && $this->PageNo() == $this->last_page_docheader) {
            $this->last_page_docheader = 0;
            $this->setPrintFooter($this->config['document']['pagefooter']);
        }
        
        // met à jour les index previous
        if (array_key_exists('index', $this->data)) {
            foreach ($this->data['index'] as &$array) {
                $array['previous'] = $array['current'];
            }
            unset($array);
        }
        
        // start new page
        $this->startPage($orientation, $format, $tocpage);
    }

    /**
     * Surcharge de la méthode.
     * Pour définir un nouveau modèle d'en-tête, il suffit d'écrire une méthode templateHeaderMethod2(), templateHeaderMethod3()...
     * en prenant modèle sur templateHeaderMethod1().
     *
     * (non-PHPdoc)
     *
     * @see TCPDF::Header()
     */
    public function Header()
    {
        if ($this->header_xobjid === false) {
            $templateHeaderMethod = 'templateHeaderMethod' . $this->getConfig('document', 'pageheader_templateId', 1);
            if (method_exists($this, $templateHeaderMethod)) {
                $this->{$templateHeaderMethod}();
            } else {
                $this->templateHeaderMethod1(); // méthode par défaut
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
     * Modèle d'en-tête de page par défaut
     */
    public function templateHeaderMethod1($param = null)
    {
        if (is_string($param) && $param == '?')
            return 'En-tête de page par défaut';
            
            // start a new XObject Template
        $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
        $headerfont = $this->getHeaderFont();
        $headerdata = $this->getHeaderData();
        $this->y = $this->header_margin;
        if ($this->rtl) {
            $this->x = $this->w - $this->original_rMargin;
        } else {
            $this->x = $this->original_lMargin;
        }
        if ($this->getConfig('document', 'pageheader_logo_visible', true) && $headerdata['logo'] && ($headerdata['logo'] != $this->getConfig('document', 'image_blank', K_BLANK_IMAGE))) {
            if (substr($headerdata['logo'], 0, 2) == '//') {
                $k_path_logo = $headerdata['logo']; // url absolue
            } else {
                $k_path_logo = rtrim($this->getConfig('document', 'url_path_images', K_PATH_IMAGES), '/') . '/' . ltrim($headerdata['logo'], '/'); // url relative
            }
            $imgtype = \TCPDF_IMAGES::getImageFileType($k_path_logo);
            if (($imgtype == 'eps') or ($imgtype == 'ai')) {
                $this->ImageEps($k_path_logo, '', '', $headerdata['logo_width']);
            } elseif ($imgtype == 'svg') {
                $this->ImageSVG($k_path_logo, '', '', $headerdata['logo_width']);
            } else {
                $this->Image(SBM_BASE_PATH . $k_path_logo, '', '', $headerdata['logo_width']);
            }
            $imgy = $this->getImageRBY();
        } else {
            $imgy = $this->y;
        }
        $cell_height = $this->getCellHeight($headerfont[2] / $this->k);
        // set starting margin for text data cell
        if ($this->getRTL()) {
            $header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
        } else {
            $header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
        }
        $cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
        $this->SetTextColorArray($this->header_text_color);
        // header title
        $this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
        $this->SetX($header_x);
        $this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
        // header string
        $this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
        $this->SetX($header_x);
        $this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
        // print an ending header line
        $this->SetLineStyle(array(
            'width' => 0.85 / $this->k,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => $headerdata['line_color']
        ));
        $this->SetY((2.835 / $this->k) + max($imgy, $this->y));
        if ($this->rtl) {
            $this->SetX($this->original_rMargin);
        } else {
            $this->SetX($this->original_lMargin);
        }
        $this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
        $this->endTemplate();
    }

    /**
     * Surcharge de la méthode
     * Cette méthode n'est appelée par la méthode setFooter() que si la propriété print_footer == true
     * Pour définir un nouveau modèle de pied de page, il suffit d'écrire une méthode templateFooterMethod2(), templateFooterMethod3()...
     * en prenant modèle sur templateFooterMethod1().
     *
     * (non-PHPdoc)
     *
     * @see TCPDF::Footer()
     */
    public function Footer()
    {
        $templateFooterMethod = 'templateFooterMethod' . $this->getConfig('document', 'pagefooter_templateId', 1);
        if (method_exists($this, $templateFooterMethod)) {
            $this->{$templateFooterMethod}();
        } else {
            $this->templateFooterMethod1(); // méthode par défaut
        }
    }

    /**
     * Modèle de pied de page par défaut
     *
     * Il y aura toujours à droite le "numéro de page / nombre de pages" (exitant dans le modèle par défaut de tcpdf)
     * On peut rajouter une chaine à gauche et une chaine au centre du pied de page.
     *
     * Le modèle de pied de page est se trouve dans $this->config['document']['pagefooter_string']
     * et s'obtient par $this->getConfig('document', 'pagefooter_string','')
     * Pour définir une chaine à gauche : @gauche{chaine}
     * Pour définir une chaine au centre : @centre{chaine}
     * Tout ce qui ne sera pas dans l'accolade de l'une de ces 2 structures sera ignoré.
     *
     * Les chaines peuvent contenir les variables suivantes :
     * %date% : date courante de création du document
     * %nombre% : nombre de lignes de données dans cette page
     * %somme(colonne)% où colonne est le rang de la colonne surlaquelle porte la somme (à partir de 1)
     * %max(colonne)%
     * %min(colonne)%
     * %moyenne(colonne)%
     * Toute autre chaine restera inchangée.
     * Attention, les valeurs non numériques de la colonne sont considérées comme 0 pour les fonctions somme, max, min et moyenne.
     *
     * @todo : Lorsqu'il y a plusieurs sources, les calculs se font sur chaque source et sont rendus s'ils ne sont pas nul
     *      
     * @param string $param            
     * @return string
     */
    public function templateFooterMethod1($param = null)
    {
        if (is_string($param) && $param == '?')
            return 'Pied de page par défaut';
        
        $cur_y = $this->y;
        $this->SetTextColorArray($this->footer_text_color);
        // set style for cell border
        $line_width = (0.85 / $this->k);
        $this->SetLineStyle(array(
            'width' => $line_width,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => $this->footer_line_color
        ));
        // print document barcode
        $barcode = $this->getBarcode();
        if (! empty($barcode)) {
            $this->Ln($line_width);
            $barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
            $style = array(
                'position' => $this->rtl ? 'R' : 'L',
                'align' => $this->rtl ? 'R' : 'L',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                'border' => false,
                'padding' => 0,
                'fgcolor' => array(
                    0,
                    0,
                    0
                ),
                'bgcolor' => false,
                'text' => false
            );
            $this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
        }
        $w_page = isset($this->l['w_page']) ? $this->l['w_page'] . ' ' : '';
        if (empty($this->pagegroups)) {
            $pagenumtxt = $w_page . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
        } else {
            $pagenumtxt = $w_page . $this->getPageNumGroupAlias() . ' / ' . $this->getPageGroupAlias();
        }
        $this->SetY($cur_y);
        // Print page number
        if ($this->getRTL()) {
            $this->SetX($this->original_rMargin);
            $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
        } else {
            $this->SetX($this->original_lMargin);
            $this->Cell(0, 0, $this->getAliasRightShift() . $pagenumtxt, 'T', 0, 'R');
        }
        // Print string
        $txt = $this->getConfig('document', 'pagefooter_string', '');
        if (! empty($txt)) {
            // remplacer les variables de la chaine
            $oCalculs = new Calculs($this->data[1]);
            $oCalculs->range($this->data['index'][1]['previous'], $this->data['index'][1]['current'] - 1);
            $txt = $oCalculs->getResultat($txt);
            
            // découpe en 2 parties
            preg_match("/@gauche{([^}]*)}/", $txt, $matches);
            $part_gauche = isset($matches[1]) ? $matches[1] : '';
            preg_match("/@centre{([^}]*)}/", $txt, $matches);
            $part_centre = isset($matches[1]) ? $matches[1] : '';
            
            // écrire le résultat
            $this->SetY($cur_y);
            if (! empty($part_gauche)) {
                if ($part_gauche == strip_tags($part_gauche)) {
                    $this->Write(0, $part_gauche, '', false, 'L');
                } else {
                    $this->writeHTML($part_gauche, true, false, true, false, 'L');
                }
            }
            if (! empty($part_centre)) {
                $this->SetX(0);
                if ($part_centre == strip_tags($part_centre)) {
                    $this->Write(0, $part_centre, '', false, 'C');
                } else {
                    $this->writeHTML($part_centre, true, false, true, false, 'C');
                }
            }
        }
    }

    /**
     * Conversion du codage de couleur.
     *
     * @param
     *            string|array
     *            Reçoit une chaine représentant le codage de la couleur au format html (codage hexa RGB)
     *            ou une chaine représentant le codage de la couleur au format rgb (chaine composée de 3 entiers de 0 à 255 séparés par des ,)
     *            ou un tableau de 3 entiers de 0 à 255 représentant le codage de la couleur au format rgb
     *            
     * @return array string la couleur codée au format RGB sous la forme d'un tableau si l'entrée est au format HTML
     *         ou la couleur codée au format HTML sous la forme d'une chaine si l'entrée est au format RGB
     */
    private function convertColor($colorInput)
    {
        if (is_string($colorInput)) {
            $is_RGB = false;
            $array = explode(',', $colorInput);
            if (count($array) == 3) {
                $is_RGB = true;
                foreach ($array as $part) {
                    $is_RGB &= is_int($part) && ($part >= 0 && $part <= 255);
                }
            }
            if ($is_RGB)
                $colorInput = $array;
        }
        
        // convert hexadecimal to RGB
        if (is_string($colorInput)) {
            // si c'est le nom d'une couleur http
            if (array_key_exists($colorInput, \TCPDF_COLORS::$webcolor)) {
                $colorInput = \TCPDF_COLORS::$webcolor[$colorInput];
            }
            $colorInput = '#' . ltrim($colorInput, '#');
            if (preg_match("/^[#]([0-9a-fA-F]{6})$/", $colorInput)) {
                $hex_R = substr($colorInput, 1, 2);
                $hex_G = substr($colorInput, 3, 2);
                $hex_B = substr($colorInput, 5, 2);
                return array(
                    hexdec($hex_R),
                    hexdec($hex_G),
                    hexdec($hex_B)
                );
            } else {
                throw new Exception(sprintf("%s (%s Ligne %d) %s n'est pas le codage HTML d'une couleur.", __METHOD__, __FILE__, __LINE__, $colorInput));
            }
        } else {
            // convert RGB to hexadecimal
            if (! is_array($colorInput)) {
                ob_start();
                var_dump($colorInput);
                $dump = html_entity_decode(strip_tags(ob_get_clean()));
                throw new Exception(sprintf("%s (%s Ligne %d) L'entrée n'est pas le codage RGB d'une couleur.\n%s", __METHOD__, __FILE__, __LINE__, $dump));
            }
            
            foreach ($colorInput as $value) {
                $hex_value = dechex($value);
                if (strlen($hex_value) < 2) {
                    $hex_value = "0" . $hex_value;
                }
                $hex_RGB .= $hex_value;
            }
            
            return "#" . $hex_RGB;
        }
    }

    /**
     * Ecrit le texte donné en style Titre{n} où n est un entier de 1 à 4
     *
     * @param integer $n
     *            numéro de Titre
     * @param string $text
     *            texte à écrire
     * @param string $align
     *            'L' pour aligner à gauche, 'C' pour centrer, 'R' pour aligner à droite
     */
    protected function Titre($n, $text, $align)
    {
        $current_font_family = $this->getFontFamily();
        $current_font_style = $this->getFontStyle();
        $current_font_size = $this->getFontSizePt();
        $current_text_color = $this->TextColor;
        $current_draw_color = $this->DrawColor;
        
        $titre = "titre$n";
        // la bordure
        $t = $titre . '_line';
        if ($this->getConfig('document', $t, 0)) {
            $t .= '_color';
            $syle = array(
                'color' => $this->convertColor($this->getConfig('document', $t, 'black'))
            );
            $this->SetLineStyle($style);
            $border = 1;
        } else {
            $border = 0;
        }
        // la police
        $t = $titre . '_font_';
        $this->SetFont($this->getConfig('document', $t . 'family', PDF_FONT_NAME_MAIN), trim($this->getConfig('document', $t . 'style', 'B')), $this->getConfig('document', $t . 'size', PDF_FONT_SIZE_MAIN));
        // la couleur
        $this->SetTextColorArray($this->convertColor($this->getConfig('document', $titre . '_text_color', 'black')));
        // le texte
        $this->Cell(0, 20, $text, $border, 1, $align);
        
        $this->setFont($current_font_family, $current_font_style, $current_font_size);
        $this->TextColor = $current_text_color;
        $this->DrawColor = $current_draw_color;
    }

    /**
     * Configuration graphique correspondant à cette section de table
     *
     * @param string $section
     *            'thead', 'tbody' ou 'tfoot'
     */
    protected function configGraphicSectionTable($section)
    {
        $this->SetFont($this->getConfig('document', 'data_font_family', PDF_FONT_NAME_DATA), trim($this->getConfig(array(
            'doctable',
            $section
        ), 'font_style', '')), $this->getConfig('document', 'data_font_size', PDF_FONT_SIZE_DATA));
        $this->SetLineWidth($this->getConfig(array(
            'doctable',
            $section
        ), 'line_width', 0.2));
        $this->SetDrawColorArray($this->convertColor($this->getConfig(array(
            'doctable',
            $section
        ), 'draw_color', 'black')));
        $this->SetFillColorArray($this->convertColor($this->getConfig(array(
            'doctable',
            $section
        ), 'fill_color', 'white')));
        $this->SetTextColorArray($this->convertColor($this->getConfig(array(
            'doctable',
            $section
        ), 'text_color', 'black')));
    }
    
    // ============= Les en-tête de document ======================
    /**
     * Modèle d'en-tête de document par défaut
     */
    public function templateDocHeaderMethod1($param = null)
    {
        if (is_string($param) && $param == '?')
            return 'En-tête de document par défaut';
        
        if ($this->getConfig('document', 'docheader_page_distincte', true)) {
            // cas d'une page distincte
            $this->SetY(100, true, true);
            $this->Titre(1, $this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE), 'C');
            $this->SetY(140, true, true);
            $this->SetFont($this->getFontFamily(), 'I');
            $this->Write(0, trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") . "\n", '', false, 'J');
        } else {
            // cas d'une section continue
            $y = $this->GetY() + $this->getConfig('document', 'docheader_margin', self::DEFAULT_SBM_DOCHEADER_MARGIN);
            $this->SetY($y);
            $tmp = $this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE);
            if (! empty($tmp)) {
                $this->Titre(1, $this->getConfig('document', 'title', self::DEFAULT_SBM_DOCUMENT_TITLE), 'C');
                $delta = trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") == '' ? self::SBM_DOCHEADER_DELTA_SMALL : self::SBM_DOCHEADER_DELTA_WIDE;
                $this->SetY($this->GetY() + $delta);
            }
            if (trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") != '') {
                if ($this->getConfig('document', 'docheader_subtitle', '') == strip_tags($this->getConfig('document', 'docheader_subtitle', ''))) {
                    $font_style = $this->getFontStyle();
                    $this->SetFont($this->getFontFamily(), 'I');
                    $this->Write(0, trim($this->getConfig('document', 'docheader_subtitle', ''), "\n") . "\n", '', false, 'J');
                    $this->SetFont($this->getFontFamily(), $font_style);
                } else {
                    $this->writeHTML($this->getConfig('document', 'docheader_subtitle', ''), true, false, true, false, '');
                }
            }
            /*
             * $this->SetLineStyle(array(
             * 'width' => 0.85 / $this->k,
             * 'cap' => 'butt',
             * 'join' => 'miter',
             * 'dash' => 0,
             * 'color' => $headerdata['line_color']
             * ));
             */
            $this->SetY((2.835 / $this->k) + $this->GetY());
        }
    }
    
    // ============= Les corps de document ======================
    /**
     * Modèle de corps de document par défaut.
     * Renvoie un identifiant du template si $param vaut '?'. Sinon, le paramètre est ignoré et le template est exécuté.
     *
     * @param string $param
     *            s'il est renseigné il doit avoir la valeur '?' (sinon, il est ignoré)
     *            
     * @return void|string Renvoie l'identifiant du template si $param == '?' sinon rien
     */
    public function templateDocBodyMethod1($param = null)
    {
        /**
         * Identifiant du template
         */
        if (is_string($param) && $param == '?') {
            return 'Corps de document par défaut';
        }
        
        /**
         * Initialisations et calculs
         */
        // lecture de la table 'doctables' pour obtenir $this->config['doctable'] = array('thead' => ..., 'tbody' => ..., 'tfoot' => ..., 'columns' => ...)
        $this->initConfigDoctable();
        
        // initialise les données
        $this->data['count'][1] = count($this->getDataForTable(1));
        $this->data['index'][1] = array(
            'previous' => 0,
            'current' => 0
        );
        $this->data['calculs'][1] = array();
        
        // prend en compte le titre de la colonne pour la largeur à prévoir
        if ($this->getConfig(array(
            'doctable',
            'thead'
        ), 'visible', false)) {
            $this->configGraphicSectionTable('thead');
            foreach ($this->getConfig('doctable', 'columns', array()) as &$column) {
                // vérifie si la largeur est suffisante pour écrire le titre
                $value_width = $this->GetStringWidth($column['thead'], $this->getConfig('document', 'data_font_family', PDF_FONT_NAME_DATA), trim($this->getConfig(array(
                    'doctable',
                    'thead'
                ), 'font_style', '')), $this->getConfig('document', 'data_font_size', PDF_FONT_SIZE_DATA));
                $value_width += $this->cell_padding['L'] + $this->cell_padding['R'];
                if ($value_width > $column['width']) {
                    $column['width'] = $value_width;
                }
                unset($column);
            }
        }
        
        // largeur de la zone d'écriture
        $pagedim = $this->getPageDimensions();
        $max_width = $pagedim['wk'] - $pagedim['lm'] - $pagedim['rm'];
        $sum_width = 0;
        foreach ($this->getConfig('doctable', 'columns', array()) as $column) {
            $sum_width += $column['width'];
        }
        if (($table_width = $this->getConfig(array(
            'doctable',
            'tbody'
        ), 'width', 'auto')) == 'auto') {
            $ratio = $sum_width > $max_width ? $max_width / $sum_width : 1;
        } else {
            $ratio = $max_width * $table_width / 100 / $sum_width;
        }
        
        // largeur des colonnes
        foreach ($this->getConfig('doctable', 'columns', array()) as &$column) {
            if ($ratio < 1) {
                $column['thead_stretch'] = 1;
                $column['tbody_stretch'] = 1;
            }
            $column['width'] *= $ratio;
            unset($column);
        }
        
        /**
         * Ecriture du document
         */
        // thead
        if ($this->getConfig(array(
            'doctable',
            'thead'
        ), 'visible', false)) {
            foreach ($this->getConfig('doctable', 'columns', array()) as $column) {
                if (is_numeric($column['thead'])) {
                    $align = $column['thead_align'] == 'standard' ? 'R' : $column['thead_align'];
                } else {
                    $align = $column['thead_align'] == 'standard' ? 'L' : $column['thead_align'];
                }
                $this->Cell($column['width'], $this->getConfig(array(
                    'doctable',
                    'thead'
                ), 'row_height'), StdLib::formatData($column['thead'], $column['thead_precision'], $column['thead_completion']), $this->getConfig(array(
                    'doctable',
                    'thead'
                ), 'cell_border'), 0, $align, 1, $this->getConfig(array(
                    'doctable',
                    'thead'
                ), 'cell_link'), $column['thead_stretch'], $this->getConfig(array(
                    'doctable',
                    'thead'
                ), 'cell_ignore_min_height'), $this->getConfig(array(
                    'doctable',
                    'thead'
                ), 'cell_calign'), $this->getConfig(array(
                    'doctable',
                    'thead'
                ), 'cell_valign'));
            }
            $this->Ln();
        }
        
        // tbody
        if ($this->getConfig(array(
            'doctable',
            'tbody'
        ), 'visible', false)) {
            $this->configGraphicSectionTable('tbody');
            $columns = $this->getConfig('doctable', 'columns', array());
            $fill = 0;
            foreach ($this->getDataForTable() as $row) {
                for ($j = 0; $j < count($row); $j ++) {
                    if (is_numeric($row[$j])) {
                        $align = $columns[$j]['tbody_align'] == 'standard' ? 'R' : $columns[$j]['tbody_align'];
                    } else {
                        $align = $columns[$j]['tbody_align'] == 'standard' ? 'L' : $columns[$j]['tbody_align'];
                    }
                    $this->Cell($columns[$j]['width'], $this->getConfig(array(
                        'doctable',
                        'tbody'
                    ), 'row_height'), StdLib::formatData($row[$j], $columns[$j]['tbody_precision'], $columns[$j]['tbody_completion']), $this->getConfig(array(
                        'doctable',
                        'tbody'
                    ), 'cell_border'), 0, $align, $fill, $this->getConfig(array(
                        'doctable',
                        'tbody'
                    ), 'cell_link'), $columns[$j]['tbody_stretch'], $this->getConfig(array(
                        'doctable',
                        'tbody'
                    ), 'cell_ignore_min_height'), $this->getConfig(array(
                        'doctable',
                        'tbody'
                    ), 'cell_calign'), $this->getConfig(array(
                        'doctable',
                        'tbody'
                    ), 'cell_valign'));
                }
                $this->data['index'][1]['current'] ++; // il faut mettre cette ligne après l'appel de Cell()
                $this->Ln();
                $fill = ! $fill;
            }
        }
        
        // tfoot
        if ($this->getConfig(array(
            'doctable',
            'tfoot'
        ), 'visible', false)) {
            $this->configGraphicSectionTable('tfoot');
            $index = 0;
            foreach ($this->getConfig('doctable', 'columns', array()) as $column) {
                // calcul sur la colonne $index
                $oCalculs = new Calculs($this->data[1], ++$index);
                $value = $oCalculs->getResultat($column['tfoot']);
                //
                if (is_numeric($value)) {
                    $align = $column['tfoot_align'] == 'standard' ? 'R' : $column['tfoot_align'];
                } else {
                    $align = $column['tfoot_align'] == 'standard' ? 'L' : $column['tfoot_align'];
                }
                $this->Cell($column['width'], $this->getConfig(array(
                    'doctable',
                    'tfoot'
                ), 'row_height'), StdLib::formatData($value, $column['tfoot_precision'], $column['tfoot_completion']), $this->getConfig(array(
                    'doctable',
                    'tfoot'
                ), 'cell_border'), 0, $align, 1, $this->getConfig(array(
                    'doctable',
                    'tfoot'
                ), 'cell_link'), $column['tfoot_stretch'], $this->getConfig(array(
                    'doctable',
                    'tfoot'
                ), 'cell_ignore_min_height'), $this->getConfig(array(
                    'doctable',
                    'tfoot'
                ), 'cell_calign'), $this->getConfig(array(
                    'doctable',
                    'tfoot'
                ), 'cell_valign'));
            }
            $this->Ln();
        }
        
        $this->Cell($sum_width * $ratio, 0, '', 'T');
    }

    protected function initConfigDoctable($ordinal_table = 1)
    {
        $table_doctables = $this->getServiceLocator()->get('Sbm\Db\System\DocTables');
        try {
            $this->config['doctable'] = $table_doctables->getConfig($this->getDocumentId(), $ordinal_table);
        } catch (\Exception $e) {
            $this->config['doctable'] = require (__DIR__ . '/default/doctables.inc.php');
        }
        
        $table_columns = $this->getServiceLocator()->get('Sbm\Db\System\DocTables\Columns');
        try {
            $this->config['doctable']['columns'] = $table_columns->getConfig($this->getDocumentId(), $ordinal_table);
        } catch (\Exception $e) {
            // pas d'en-tête, pas de pied, colonnes proportionnelles à la taille du contenu
            $this->config['doctable']['thead']['visible'] = $this->config['doctable']['tfoot']['visible'] = false;
        }
    }

    /**
     * Renvoie le tableau des données pour la table $ordinal_table.
     * Initialise le tableau s'il est vide.
     *
     * @param int $ordinal_table            
     * @param boolean $force            
     * @throws Exception
     * @return array
     */
    protected function getDataForTable($ordinal_table = 1, $force = false)
    {
        if ($force || empty($this->data[$ordinal_table])) {
            $this->data[$ordinal_table] = array();
            if ($this->getRecordSourceType() == 'T') {
                // La source doit être enregistrée dans le ServiceManager (table ou vue MySql) sinon exception
                $table = $this->getRecordSourceTable();
                // lecture de la description des colonnes. Si absente, on configure toutes les colonnes de la source
                $table_columns = $this->getConfig('doctable', 'columns', array());
                if (empty($table_columns)) {
                    foreach ($table->getColumnsNames() as $column_name) {
                        $column = require (__DIR__ . '/default/doccolumns.inc.php');
                        $column['thead'] = $column['tbody'] = $column_name;
                        $table_columns[] = $column;
                    }
                    $this->config['doctable']['columns'] = $table_columns;
                }
                // prépare les filtres pour le décodage des données (notamment booléennes)
                foreach ($table_columns as &$column) {
                    $column['filter'] = preg_replace(array(
                        '/^\s+/',
                        '/\s+$/'
                    ), '', $column['filter']);
                    if (! empty($column['filter'])) {
                        $column['filter'] = StdLib::getArrayFromString($column['filter']);
                    } else {
                        $column['filter'] = array();
                    }
                    unset($column);
                }
                // lecture des données et calcul des largeurs de colonnes
                foreach ($table->fetchAll($this->getWhere(), $this->getOrderBy()) as $row) {
                    $ligne = array();
                    foreach ($table_columns as &$column) {
                        $ligne[] = $value = StdLib::translateData($row->{$column['tbody']}, $column['filter']);
                        // adapte la largeur de la colonne si nécessaire
                        $value_width = $this->GetStringWidth($value, $this->getConfig('document', 'data_font_family', PDF_FONT_NAME_DATA), '', $this->getConfig('document', 'data_font_size', PDF_FONT_SIZE_DATA));
                        $value_width += $this->cell_padding['L'] + $this->cell_padding['R'];
                        if ($value_width > $column['width']) {
                            $column['width'] = $value_width;
                        }
                        unset($column);
                    }
                    $this->data[$ordinal_table][] = $ligne;
                }
                $this->config['doctable']['columns'] = $table_columns;
            } else {
                // c'est une requête Sql. Il n'y a pas de description des colonnes dans la table doccolumns. On va en créer un par défaut.
                $this->data[$ordinal_table] = array();
                if ($this->getServiceLocator()->has($this->getConfig('document', 'recordSource', ''))) {
                    $sql = $this->getConfig('document', 'recordSource', '');
                }
                $dbAdapter = $this->getServiceLocator()
                    ->get('Sbm\Db\DbLib')
                    ->getDbAdapter();
                try {
                    foreach ($dbAdapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE) as $row) {
                        // $row est un ArrayObject
                        $ligne = array();
                        $table_columns = array();
                        foreach ($row as $key => $value) {
                            if (empty($table_columns[$key])) {
                                $column = require (__DIR__ . '/default/doccolumns.inc.php');
                                $column['thead'] = trim($column['tbody'] = $key, '`');
                                $table_columns[] = $column;
                            }
                            $ligne[] = $value;
                            // adapte la largeur de la colonne si nécessaire
                            $value_width = $this->GetStringWidth($value, PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);
                            if ($value_width > $table_columns[$key]['width']) {
                                $table_columns[$key]['width'] = $value_width;
                            }
                        }
                        $this->data[$ordinal_table][] = $ligne;
                    }
                    $this->config['doctable']['columns'] = $table_columns;
                    $this->config['doctable']['thead']['visible'] = true;
                    $this->config['doctable']['tfoot']['visible'] = false;
                } catch (\Exception $e) {
                    $message = sprintf("Impossible d\'exécuter la requête décrite dans ce document.\n%s", $sql);
                    throw new Exception($message, $e->getCode(), $e->getPrevious());
                }
            }
        }
        
        return $this->data[$ordinal_table];
    }
    
    // ============= Les pieds de document ======================
    
    /**
     * Modèle de pied de document par défaut
     * Les variables de chaine traitées sont :
     * %nombre% : nombre de ligne de données
     * %numero% : numéro de la source (uniquement s'il y a plusieurs sources)
     */
    public function templateDocFooterMethod1($param = null)
    {
        if (is_string($param) && $param == '?') {
            return 'Pied de document par défaut';
        }
        
        // texte à écrire
        $oCalculs = new Calculs($this->data[1]);
        $txt = $oCalculs->getResultat(trim($this->getConfig('document', 'docfooter_string', ''), "\n") . "\n");
        
        // mise en page
        if ($this->getConfig('document', 'docfooter_page_distincte', false)) {
            // cas d'une page distincte
            $this->SetY($this->GetY() + $this->getConfig('document', 'docfooter_margin', self::DEFAULT_SBM_DOCFOOTER_MARGIN));
            $tmp = $this->getConfig('document', 'docfooter_title', '');
            if (! empty($tmp)) {
                $this->Titre(1, $this->getConfig('document', 'docfooter_title', ''), 'C');
                $this->SetY(140, true, true);
            }
            if (! empty($txt)) {
                $this->SetFont($this->getFontFamily(), 'I');
                // écrire le résultat
                if ($txt == strip_tags($txt)) {
                    $this->Write(0, trim($txt, "\n") . "\n");
                } else {
                    $this->writeHTML($txt, true, false, true, false, '');
                }
            }
        } else {
            // cas d'une section continue
            $current_page = $this->PageNo();
            $delta = $this->getConfig('document', 'docfooter_margin', self::DEFAULT_SBM_DOCFOOTER_MARGIN);
            if ($this->getConfig('document', 'docfooter_insecable', true)) {
                // on réserve la place pour la marge, le titre (si non vide) et 3 lignes (si docfooter_string non vide)
                $tmp = $this->getConfig('document', 'docfooter_title', '');
                if (! empty($tmp)) {
                    $delta += self::SBM_DOCFOOTER_INSECABLE_DELTA_TITLE;
                }
                if (! empty($txt)) {
                    $delta += self::SBM_DOCFOOTER_INSECABLE_DELTA_NBLIGNES * $this->getCellHeight($this->FontSize);
                }
            }
            $this->checkPageBreak($delta);
            if ($current_page == $this->PageNo()) {
                $this->SetY($this->GetY() + $this->getConfig('document', 'docfooter_margin', self::DEFAULT_SBM_DOCFOOTER_MARGIN));
            }
            $tmp = $this->getConfig('document', 'docfooter_title', '');
            if (! empty($tmp)) {
                $this->Titre(1, $this->getConfig('document', 'docfooter_title', ''), 'L');
            }
            if (! empty($txt)) {
                // écrire le résultat
                if ($txt == strip_tags($txt)) {
                    $this->Write(0, trim($txt, "\n") . "\n");
                } else {
                    $this->writeHTML($txt, true, false, true, false, '');
                }
            }
        }
    }

    private function getDataCalculTotal($key)
    {
        $function = $this->data['calculs'][1][$key]['function'];
        $column = $this->data['calculs'][1][$key]['column'];
        $column --; // indexation des colonnes à partir de 0
        switch ($function) {
            case 'somme':
                $result = 0;
                for ($j = 0; $j < $this->data['count'][1]; $j ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur)) {
                        $result += $valeur;
                    }
                }
                break;
            case 'max':
                $result = - PHP_INT_MAX;
                for ($j = 0; $j < $this->data['count'][1]; $j ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur) && $valeur > $result) {
                        $result = $valeur;
                    }
                }
                break;
            case 'min':
                $result = PHP_INT_MAX;
                for ($j = 0; $j < $this->data['count'][1]; $j ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur) && $valeur < $result) {
                        $result = $valeur;
                    }
                }
                break;
            case 'moyenne':
                $tmp_somme = 0;
                for ($j = 0, $tmp_nombre = 0; $j < $this->data['count'][1]; $j ++, $tmp_nombre ++) {
                    $valeur = $this->data[1][$j][$column];
                    if (is_numeric($valeur)) {
                        $tmp_somme += $valeur;
                    }
                }
                $result = $tmp_somme / $tmp_nombre;
                break;
            default:
                $result = - PHP_INT_MAX;
                break;
        }
        return $result;
    }
}

