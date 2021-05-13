<?php
/**
 * Tableau Html
 *
 * Chaque document de ce type est associé à un layout HTML décrivant le document final.
 * Les variables sont codées %variable%
 * Les données sont passées par
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document/Template
 * @filesource TableHtml.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 févr. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Document\Template;

use SbmPdf\Model\Document;

class TableHtml extends Document\AbstractDocument
{

    const PDFMANAGER_ID = 'tableHtml';

    public static function description()
    {
    }

    protected function init()
    {
    }

    protected function getData()
    {
    }

    protected function templateDocumentFooter()
    {
    }

    protected function templateDocumentHeader()
    {
    }

    protected function templateDocumentBody()
    {
    }
}