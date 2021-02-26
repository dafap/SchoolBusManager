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
 * @date 18 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Document;

use SbmBase\Model\StdLib;
use SbmPdf\Model\Tcpdf\Tcpdf;
use Zend\Stdlib\Parameters;
use SbmPdf\Service\PdfManager;
use Zend\Db\Sql\Where;

abstract class AbstractDocument implements DocumentInterface
{
    use DocumentTrait,\SbmCommun\Model\Traits\DebugTrait;

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
     * @var mixed
     */
    protected $data;

    protected $majIndexAddPage;

    public function __construct()
    {
        $this->params = [];
        $this->pdf = new Tcpdf();
        $this->pdf->SetCreator('TCPDF');
        $this->pdf->SetAuthor('School Bus Manager');
        $this->documentId = 0;
        $this->config = new Parameters();
        // set image scale factor
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set some language-dependent strings (optional)
        $fileLanguage = StdLib::concatPath(StdLib::findParentPath(__DIR__, 'lang'),
            PDF_LANG . '.php');
        if (@is_file($fileLanguage)) {
            $this->pdf->setLanguageArray(require $fileLanguage);
        }
        $this->majIndexAddPage = null;
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
    public function setDocumentId(int $documentId): self
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
    public function setParams(Parameters $params): self
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
    public function setConfig(string $key, array $config): self
    {
        $this->config->set($key, new Parameters($config));
        return $this;
    }

    /**
     * Lance la construction du document pdf
     */
    public function render(): string
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'),
            'tablesimple.log');
        $this->debugLog(__METHOD__);
        // Lecture des tables nécessaires à l'initialisation
        $this->init();
        $this->pdf->setConfig($this->config)
            ->setMajIndexAddPage($this->majIndexAddPage)
            ->setPageHeaderMethod(function(){}); // @todo à terminer

        // En-tête de document
        if ($this->config->document->docheader) {
            $this->sectionDocumentHeader();
        }

        // corps du document
        $this->sectionDocumentBody();

        // pied du document
        if ($this->config->document->docfooter) {
            $this->sectionDocumentFooter();
        }
        return (string) $this->pdf->getPDFData();
    }

    protected function sectionDocumentHeader()
    {
        $this->pdf->setSectionDocument($this->pdf::SECTION_DOCHEADER);
        $this->pdf->setPrintHeader(
            $this->config->document->get('docheader_pageheader', false));
        $this->pdf->setPrintFooter(
            $this->config->document->get('docheader_pagefooter', false));
        $this->pdf->AddPage();
        $this->templateDocumentHeader();
        $this->pdf->savePageNo();
    }

    protected function sectionDocumentBody()
    {
        $this->pdf->setSectionDocument($this->pdf::SECTION_DOCBODY);
        $current_font_colors = $this->pdf->saveFontColors();
        // gestion du header, du footer et du AddPage
        if ($this->config->document->get('docheader', false)) {
            // il y a une page d'en-tête
            if ($this->config->document->get('pageheader', false) !=
                $this->config->document->get('docheader_pageheader', false)) {
                // configuration différente de l'en-tête de page : on la charge
                $this->pdf->resetHeaderTemplate();
                $this->pdf->setPrintHeader(
                    $this->config->document->get('pageheader', false));
            }
            if ($this->config->document->get('docheader_page_distincte', false)) {
                // page distincte
                $this->pdf->AddPage();
            }
        } else {
            $this->pdf->setPrintHeader($this->config->document->get('pageheader', false));
            $this->pdf->setPrintFooter($this->config->document->get('pagefooter', false));
            $this->pdf->AddPage();
        }
        $this->templateDocumentBody();
        $this->pdf->restoreFontColors($current_font_colors);
    }

    protected function sectionDocumentFooter()
    {
        $this->pdf->setSectionDocument($this->pdf::SECTION_DOCFOOTER);
        // page distincte pour le pied de document
        if ($this->config->document->get('docfooter_page_distincte', false)) {
            if ($this->config->document->get('pageheader', false) !=
                $this->config->document->get('docfooter_pageheader', false)) {
                $this->pdf->resetHeaderTemplate();
                $this->pdf->setPrintHeader(
                    $this->config->document->get('docfooter_pageheader', false));
            }
            $this->pdf->AddPage();
        } else {
            $this->pdf->SetY($this->pdf->GetY(), true);
        }
        // dans tous les cas, on place le pied de page correctement
        $this->pdf->setPrintFooter(
            $this->config->document->get('docfooter_pagefooter', false));
        $this->templateDocumentFooter();
    }

    /**
     * Renvoie une chaine ou un tableau définissant l'ordre de tri des données
     *
     * @return string|array
     */
    protected function getOrderBy()
    {
        $orderBy = $this->params->get('orderBy', $this->config->document->orderBy);
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