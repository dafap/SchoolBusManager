<?php
/**
 * Interface des Model\Db\ObjectData
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource ObjectDataInterface.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData;

use Zend\Paginator\Adapter\Iterator;

interface ObjectDataInterface
{

    /**
     * Peut être soit traversable|array
     * 
     * @abstract
     *
     * @param Iterator|IteratorAggregate|array $dataSource            
     * @return ArrayIterator
     */
    public function exchangeArray($dataSource);

    /**
     * Renvoie les données sous forme d'un tableau
     * 
     * @abstract
     *
     * @return array
     */
    public function getArrayCopy();

    /**
     * Donne l'id de la dataSource si elle existe, false si non
     * 
     * @abstract
     *
     * @return mixed
     */
    public function getId();

    /**
     * Renvoie le nom du champ Id de l'objet
     * 
     * @abstract
     *
     * @return string
     */
    public function getIdFieldName();

    /**
     * Renvoie l'iterator
     * 
     * @abstract
     *
     * @return Iterator
     */
    public function getIterator();

    /**
     * Renvoi le nom de l'objet
     * 
     * @abstract
     *
     * @return string
     */
    public function getObjName();

    /**
     * Enregistre la masque de champs à utiliser pour la méthode exchangeArray()
     * 
     * @param array $array_mask            
     */
    public function setArrayMask($array_mask);
}