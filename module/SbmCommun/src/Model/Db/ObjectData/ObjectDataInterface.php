<?php
/**
 * Interface des Model\Db\ObjectData
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource ObjectDataInterface.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\ObjectData;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

interface ObjectDataInterface
{
    /**
     * Nombre d'éléments définis dans la propriété dataSource
     *
     * @see \Countable::count()
     *
     * @return int
     */
    public function count();

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
     * Renvoie le masque utilisé dans exchangeArray()
     *
     * @return array
     */
    public function getArrayMask();
    
    /**
     * Renvoie le tableau des champs calculés
     *
     * @return array
     */
    public function getCalculateFields();

    /**
     * Donne l'id de la dataSource si elle existe, false si non
     *
     * @abstract
     *
     * @return mixed
     */
    public function getId();

    /**
     * Renvoie le(s) nom(s) du(des) champ(s) Id de l'objet
     *
     * @abstract
     *
     * @return string|array
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
     * Lorsque $id est une chaine composée de plusieurs champs séparés par |
     * la méthode renvoie un tableau associatif conforme à l'id_field_name.
     * Dans les autres cas, la méthode renvoie $id inchangé.
     *
     * Renvoie un id correct :
     * - scalaire si id_field_name est un scalaire,
     * - tableau associatif si id_field_name est un tableau
     * On doit s'assurer avant que l'id est valide
     *
     * @param int|string|array $id
     *
     * @return int|string|array
     */
    public function getValidId($id);
    
    /**
     * Indique si l'objet courant a changé par rapport à l'ancien objet passé en paramètre
     *
     * @param ObjectDataInterface $old_obj
     *
     * @return boolean
     */
    public function isUnchanged(ObjectDataInterface $old_obj);
    
    /**
     * Devra être surchargé si un contrôle de construction d'id est nécessaire.
     * C'est le cas lorsque la primary key est composée de plusieurs champs.
     *
     * @param int|string|array $id
     *
     * @return boolean
     */
    public function isValidId($id);
    
    /**
     * Enregistre la liste des champs avec l'indicateur is_nullable (boolean)
     * Lance une exception si le paramètre n'est pas un tableau.
     *
     * @param array $are_nullable
     * @throws Exception\ExceptionInterface
     */
    public function setAreNullable($are_nullable = []);

    /**
     * Enregistre la masque de champs à utiliser pour la méthode exchangeArray()
     *
     * @param array $array_mask            
     */
    public function setArrayMask($array_mask);
    
    /**
     * Initialise la propriété calculate_fields avec le tableau fourni.
     * 
     * @param array $array
     */
    public function setCalculateFields($array);
    
    /**
     * Initialise la propriété max_length_array
     *
     * @param array $aMaxLength
     * @return \SbmCommun\Model\Db\ObjectData\AbstractObjectData
     */
    public function setMaxLengthArray($aMaxLength);
    
    /**
     * Ajoute le champ fourni comme paramètre dans la propriété calculate_fields
     * Lance une exception si le paramètre n'est pas une chaine de caractères.
     *
     * @param string $str
     *
     * @throws Exception\ExceptionInterface
     */
    public function addCalculateField($str);
}