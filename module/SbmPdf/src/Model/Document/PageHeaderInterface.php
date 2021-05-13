<?php
/**
 * Interface d'un objet permettant de créer une entête de page.
 * Un tel objet est nécessaire dans Tcpdf.
 *
 * Mise en oeuvre :
 * La classe est créée dans le Template du document (voir SbmPdf\Model\Document\Template)
 * dans la méthode init() et est passée au Tcpdf par la méthode
 * Tcpdf::setPageHeaderObject(). On passe les propriétés params, config et
 * data du Template. Attention, la propriété data est passée par référence.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document
 * @filesource PageHeaderInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmPdf\Model\Document;

use SbmPdf\Model\Tcpdf\Tcpdf;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Parameters;

/**
 *
 * @author alain
 *
 */
interface PageHeaderInterface
{

    /**
     * à appeler dans la méthode init() du template du document
     *
     * @param \Zend\Stdlib\Parameters $params
     * @param \Zend\Stdlib\Parameters $config
     * @param array $data
     */
    public function __construct(Parameters $params, Parameters $config, ArrayObject $data);

    /**
     * automatiquement appelé dans la méthode Tcpdf::Header() pour créer un header_xobj
     *
     * @param \SbmPdf\Model\Tcpdf\Tcpdf $pdf
     */
    public function render(Tcpdf $pdf);

    /**
     * automatiquement appelé dans la méthode Tcpdf::AddPage() pour savoir si le titre ou
     * la string ont été modifiés.
     */
    public function hasChanged();

    /**
     * automatiquement appelé dans la méthode Tcpdf::setPageHeaderObject() pour
     * initialiser la marge du haut de l'entête
     */
    public function getMarginTop();

    /**
     * automatiquement appelé dans la méthode Tcpdf::setPageHeaderObject() pour
     * initialiser la police de l'entête
     */
    public function getFont();
}

