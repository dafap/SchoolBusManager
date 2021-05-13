<?php
/**
 * Classe abstraite base pour les templates
 *
 * Ces classes sont appelées par le plugin \SbmPdf\Mvc\Controller\Plugin\Pdf
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document
 * @filesource AbstractDocument.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 mars 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Document;

use SbmBase\Model\StdLib;
use SbmPdf\Model\Tcpdf\Tcpdf;
use Zend\Stdlib\Parameters;
use SbmPdf\Service\PdfManager;
use Zend\Db\Sql\Where;
use Zend\Stdlib\ArrayObject;

abstract class AbstractDocument implements DocumentInterface
{

    const DEFAULT_SBM_DOCUMENT_TITLE = 'Liste';

    /**
     *
     * @var PdfManager
     */
    protected $pdf_manager;

    /**
     *
     * @var Tcpdf
     */
    protected $pdf;

    /**
     * Identifiant du document dans les tables système (documents, doctables, doclabels,
     * docfields, doccolumns, docaffectations)
     *
     * @var int
     */
    protected $documentId;

    /**
     *
     * @var Parameters
     */
    protected $config;

    /**
     *
     * @var Parameters
     */
    protected $params;

    /**
     * Les données à traiter dans ce document
     *
     * @var ArrayObject
     */
    protected $data;

    /**
     *
     * @var PageHeaderInterface
     */
    protected $oPageHeader;

    protected $majIndexAddPage;

    public function __construct()
    {
        $this->data = new ArrayObject();
        $this->documentId = 0;
        $this->params = new Parameters();
        $this->config = new Parameters();
        $this->majIndexAddPage = null;
        $this->pdf = new Tcpdf();
        $this->pdf->SetCreator('TCPDF');
        $this->pdf->SetAuthor('School Bus Manager');
        // set image scale factor
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set some language-dependent strings (optional)
        $fileLanguage = StdLib::concatPath(StdLib::findParentPath(__DIR__, 'lang'),
            PDF_LANG . '.php');
        if (@is_file($fileLanguage)) {
            $this->pdf->setLanguageArray(require $fileLanguage);
        }
    }

    /**
     * Recherche une propriété du document dans $params.
     * Si elle n'est pas présente elle recherche dans $config. Sinon elle renvoie la
     * valeur par défaut.
     *
     * @param string $configSection
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getProperty(string $configSection, string $key, $default)
    {
        return $this->params->get($key,
            $this->config->{$configSection}->get($key, $default));
    }

    /**
     * Renvoie le libellé (string) et la description (string formatée html) du document
     */
    abstract public static function description();

    /**
     * Complète la propriété $config par la lecture des tables du document
     */
    abstract protected function init();

    /**
     * Fournit les données alimentant le document lors d'un publipostage
     */
    abstract protected function getData();

    /**
     * Construit l'entête du document
     */
    abstract protected function templateDocumentBody();

    /**
     * Construit le corps du document
     */
    abstract protected function templateDocumentHeader();

    /**
     * Construit le pied du document
     */
    abstract protected function templateDocumentFooter();

    /**
     * Initialisation qui suit immédiatement le constructeur car c'est un objet
     * 'invokable' en service_manager
     *
     * @param \SbmPdf\Service\PdfManager $pdf_manager
     * @return self
     */
    public function setPdfManager(PdfManager $pdf_manager): self
    {
        $this->pdf_manager = $pdf_manager;
        return $this;
    }

    /**
     * Initialise le documentId
     *
     * @param int $documentId
     * @return \SbmPdf\Model\Document\AbstractDocument
     */
    public function setDocumentId(int $documentId)
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * Injection des paramètres d'appel
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\DocumentInterface::setParams()
     */
    public function setParams(Parameters $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Injection de la configuration du document (provenant de la table 'documents')
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\DocumentInterface::setConfig()
     */
    public function setConfig(string $key, array $config)
    {
        $this->config->set($key, new Parameters($config));
        return $this;
    }

    /**
     * Lance la construction du document pdf
     */
    public function render(): string
    {
        // configuration du pdf à partir des params et config->document
        $this->pdf->setProperties(
            [
                'setPageFormat' => [
                    $this->getProperty('document', 'page_format', PDF_PAGE_FORMAT),
                    $this->getProperty('document', 'page_orientation',
                        PDF_PAGE_ORIENTATION)
                ],
                'SetTitle' => $this->getProperty('document', 'title',
                    self::DEFAULT_SBM_DOCUMENT_TITLE),
                'SetSubject' => $this->getProperty('document', 'subject', ''),
                'SetKeywords' => $this->getProperty('document', 'keywords', 'SBM, TS'),
                'setPrintHeader' => $this->getProperty('document', 'pageheader', false),
                'setPrintFooter' => $this->getProperty('document', 'pagefooter', false),
                'SetMargins' => [
                    $this->getProperty('document', 'page_margin_left', PDF_MARGIN_LEFT),
                    $this->getProperty('document', 'page_margin_top', PDF_MARGIN_TOP),
                    $this->getProperty('document', 'page_margin_right', PDF_MARGIN_RIGHT)
                ],
                'SetAutoPageBreak' => [
                    true,
                    $this->getProperty('document', 'page_margin_bottom', PDF_MARGIN_BOTTOM)
                ],
                'SetDefaultMonospacedFont' => $this->getProperty('document',
                    'default_font_monospaced', PDF_FONT_MONOSPACED),
                'SetFont' => [
                    $this->getProperty('document', 'main_font_family', PDF_FONT_NAME_DATA),
                    trim($this->getProperty('document', 'main_font_style', '')),
                    $this->getProperty('document', 'main_font_size', PDF_FONT_SIZE_DATA)
                ]
            ]);
        // Lecture des tables nécessaires à l'initialisation et mise en place du
        // pageHeader et du pageFooter
        $this->init();
        $this->pdf->setConfig($this->config)->setMajIndexAddPage($this->majIndexAddPage);
        // création des pages
        if ($this->getProperty('document', 'docheader', false)) {
            $this->sectionDocumentHeader();
        }
        // corps du document
        try {
            $this->sectionDocumentBody();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        // pied du document
        if ($this->getProperty('document', 'docfooter', false)) {
            $this->sectionDocumentFooter();
        }
        return (string) $this->pdf->getPDFData();
    }

    protected function sectionDocumentHeader()
    {
        $this->pdf->setSectionDocument($this->pdf::SECTION_DOCHEADER);
        $this->pdf->setPrintHeader(
            $this->getProperty('document', 'docheader_pageheader', false));
        $this->pdf->setPrintFooter(
            $this->getProperty('document', 'docheader_pagefooter', false));
        $this->pdf->AddPage();
        $this->templateDocumentHeader();
        $this->pdf->savePageNo();
    }

    protected function sectionDocumentBody()
    {
        $this->pdf->setSectionDocument($this->pdf::SECTION_DOCBODY);
        $current_font_colors = $this->pdf->saveFontAndColors();
        // gestion du header, du footer et du AddPage
        if ($this->getProperty('document', 'docheader', false)) {
            // il y a une page d'en-tête
            if ($this->getProperty('document', 'pageheader', false) !=
                $this->getProperty('document', 'docheader_pageheader', false)) {
                // configuration différente de l'en-tête de page
                $this->pdf->resetHeaderTemplate();
                $this->pdf->setPrintHeader(
                    $this->getProperty('document', 'pageheader', false));
            }
            if ($this->getProperty('document', 'pagefooer', false) !=
                $this->getProperty('document', 'docheader_pagefooter', false)) {
                // configuration différente du pied de page
                $this->pdf->setPrintFooter(
                    $this->getProperty('document', 'pagefooter', false));
            }
            if ($this->getProperty('document', 'docheader_page_distincte', false)) {
                // page distincte
                $this->pdf->AddPage();
            }
        } else {
            $this->pdf->setPrintHeader(
                $this->getProperty('document', 'pageheader', false));
            $this->pdf->setPrintFooter(
                $this->getProperty('document', 'pagefooter', false));
            $this->pdf->AddPage();
        }
        // corps du document
        $this->templateDocumentBody();
        $this->pdf->restoreFontAndColors($current_font_colors);
    }

    protected function sectionDocumentFooter()
    {
        $this->pdf->setSectionDocument($this->pdf::SECTION_DOCFOOTER);
        // page distincte pour le pied de document
        if ($this->getProperty('document', 'docfooter_page_distincte', false)) {
            if ($this->getProperty('document', 'pageheader', false) !=
                $this->getProperty('document', 'docfooter_pageheader', false)) {
                $this->pdf->resetHeaderTemplate();
                $this->pdf->setPrintHeader(
                    $this->getProperty('document', 'docfooter_pageheader', false));
            }
            $this->pdf->AddPage();
        } else {
            $this->pdf->SetY($this->pdf->GetY(), true);
        }
        // dans tous les cas, on place le pied de page correctement
        $this->pdf->setPrintFooter(
            $this->getProperty('document', 'docfooter_pagefooter', false));
        $this->templateDocumentFooter();
    }

    /**
     * Renvoie une chaine ou un tableau définissant l'ordre de tri des données
     *
     * @return string|array
     */
    protected function getOrderBy()
    {
        $orderBy = $this->getProperty('document', 'orderBy', '');
        if (! empty($orderBy) && is_string($orderBy)) {
            $orderBy = explode(',', $orderBy);
            array_walk($orderBy, function (&$item) {
                $item = trim($item);
            });
        }
        return $orderBy;
    }

    /**
     * Renvoie le where indiqué dans $params (Where vide si absent)
     * Rajoute comme un Literal et après son décodage le filtre indiqué dans
     * $config->document
     *
     * @return \Zend\Db\Sql\Where
     */
    protected function getWhere()
    {
        $where = $this->params->get('where', new Where());
        $filter = $this->decodeSource((string) $this->config->document->filter);
        if (! empty($filter)) {
            $where->literal(sprintf('(%s)', $this->decodeSource($filter)));
        }
        return $where;
    }
}