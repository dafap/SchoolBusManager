<?php
/**
 * Classe implémentant un ArraySerializableInterface
 * 
 * (présente les méthodes exchangeArray() et getArrayCopy()
 * 
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/TestAsset
 * @filesource ObjectArraySerializable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\TestAsset;

use Zend\Stdlib\ArraySerializableInterface;

class ObjectArraySerializable implements ArraySerializableInterface
{

    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        $this->data = $array;
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->data;
    }
}