<?php
/**
 * Interface d'un objet permettant de créer un pied de page.
 * Un tel objet est nécessaire dans Tcpdf.
 *
 * Mise en oeuvre :
 * La classe est créée dans le Template du document (voir SbmPdf\Model\Document\Template)
 * dans la méthode init() et est passée au Tcpdf par la méthode
 * Tcpdf::setPageFooterObject(). On passe les propriétés params, config et
 * data du Template. Attention, la propriété data est passée par référence.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document
 * @filesource PageFooterInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmPdf\Model\Document;

use SbmPdf\Model\Tcpdf\Tcpdf;
use SbmPdf\Model\Element\ProcessFeatures;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Parameters;

/**
 *
 * @author alain
 *
 */
interface PageFooterInterface
{

    public function __construct(Parameters $params, Parameters $config, ArrayObject $data,
        ProcessFeatures $oProcess);

    public function getFont();

    public function getMargin();

    public function getTextColorArray();

    public function getLineColorArray();

    public function isVisible(): bool;

    public function render(Tcpdf $pdf);
}

