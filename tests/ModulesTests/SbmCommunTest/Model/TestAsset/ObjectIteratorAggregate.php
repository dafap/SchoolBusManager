<?php
/**
 * Classe implémentant l'interface IteratorAggregate
 *
 * 
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/TestAsset
 * @filesource ObjectIteratorAggregate.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\TestAsset;

class ObjectIteratorAggregate implements \IteratorAggregate
{

    /**
     * Définit les propriétés publiques de l'objet
     *
     * @param array $array
     *            tableau associatif
     */
    public function __construct($array = [])
    {
        foreach ($array as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}