<?php
/**
 * Décrit les coordonnées d'une cellule ou d'une zone dans un tableau 2x2
 *
 * @project sbm
 * @package SbmPdf/src/Model/Element
 * @filesource Coordinate.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 janv. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Element;

class Coordinate implements ElementInterface
{

    /**
     *
     * @var ElementInterface
     */
    private $element;

    private $container;

    public function __construct(ElementInterface $element, array $container = [])
    {
        $this->element = $element;
        $this->container = $container;
    }

    public function __get(string $param)
    {
        if (! array_key_exists($param, $this->container)) {
            throw new \SbmPdf\Model\Exception\OutOfRangeException(
                'Argument invalide. Cette propriété n\'existe pas.');
        }
        return $this->container[$param];
    }

    public function __set(string $param, $value)
    {
        $this->container[$param] = $value;
    }
}