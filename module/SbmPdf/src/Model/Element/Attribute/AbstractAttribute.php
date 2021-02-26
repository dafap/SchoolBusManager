<?php
/**
 * Classe abstraite pour les classes d'attributs
 *
 * @project    sbm
 * @package    SbmPdf/src/Model/Element/Attribute
 * @filesource AbstractAttribute.php
 * @encodage   UTF-8
 * @author     DAFAP Informatique - Alain Pomirol <dafap@free.fr>
 * @date       19 fév. 2021
 * @version    2021-2.6.1
 */
namespace SbmPdf\Model\Element\Attribute;

/**
 * Tcpdf\Extension\Attribute\AbstractAttribute
 *
 * @author naitsirch <>
 */
abstract class AbstractAttribute
{

    /**
     * élément sur lequel le background s'applique
     *
     * @var \SbmPdf\Model\Element\ElementInterface
     */
    private $element;

    /**
     *
     * @param \SbmPdf\Model\Element\ElementInterface $element
     */
    public function __construct($element)
    {
        $this->element = $element;
    }

    /**
     *
     * @return \SbmPdf\Model\Element\ElementInterface
     */
    public function __invoke()
    {
        return $this->element;
    }
}
