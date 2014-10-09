<?php
/**
 * Classe abstraite pour définir les objets data à manipuler dans les tables et les formulaires
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource AbstractObjectData.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData;

use Iterator;
use IteratorAggregate;
use ArrayIterator;
use SbmCommun\Model\Db\ObjectData\Exception;

abstract class AbstractObjectData implements ObjectDataInterface
{

    /**
     * Données de l'objet
     *
     * @var null array Iterator IteratorAggregate
     */
    protected $dataSource = null;

    /**
     * Nom de l'objet à initialiser dans le constructeur
     *
     * @var string
     */
    private $obj_name;

    /**
     * Nom du champ id
     *
     * @var string
     */
    private $id_field_name;

    /**
     * Masque servant de modèle pour la composition de la donnée dans la méthode exchangeArray()
     *
     * @var unknown_type
     */
    private $array_mask = array();

    /**
     *
     * @param string $param            
     * @throws Exception
     * @return unknown
     */
    public function __get($param)
    {
        if ($this->dataSource instanceof \ArrayIterator) {
            if ($this->dataSource->offsetExists($param)) {
                return $this->dataSource->offsetGet($param);
            } else {
                throw new Exception(sprintf('Impossible de trouver la propriété %s.', $param));
            }
        } else {
            foreach ($this->dataSource as $key => $value) {
                if ($param == $key)
                    return $value;
            }
            throw new Exception(sprintf('Impossible de trouver la propriété %s.', $param));
        }
    }

    /**
     * Affectaion par défaut
     *
     * @param string $param            
     * @param unknown $valeur            
     * @throws Exception
     */
    public function __set($param, $valeur)
    {
        if ($this->dataSource instanceof \ArrayIterator) {           
            $this->dataSource->offsetSet($param, $valeur);
        } else {
            foreach ($this->dataSource as $key => &$value) {
                if ($param == $key) {
                    $value = $valeur;
                    return;
                }
            }
            throw new Exception(sprintf('Impossible de trouver la propriété %s.', $param));
        }
    }

    /**
     * Enregistre le nom de l'objet
     *
     * @param string $name            
     * @return void
     */
    protected function setObjName($name)
    {
        $this->obj_name = $name;
    }

    /**
     * Enregister le nom du champ Id de l'objet
     *
     * @param string $name            
     * @return void
     */
    protected function setIdFieldName($name)
    {
        $this->id_field_name = $name;
    }

    /**
     * Enregistre le masque à utiliser pour la méthode exchangeArray()
     *
     * @param array $array_mask            
     *
     * @throws Exception
     * @see Bdts\Model\ObjectData.ObjectDataInterface::setArrayMask()
     */
    public function setArrayMask($array_mask = array())
    {
        if (! is_array($array_mask)) {
            throw new Exception(__METHOD__ . _(' - ArrayMask provided is not an array'));
        }
        $this->array_mask = $array_mask;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Bdts\Model.ObjectDataInterface::exchangeArray()
     */
    public function exchangeArray($dataSource)
    {
        if ($dataSource instanceof IteratorAggregate) {
            $dataSource = iterator_to_array($dataSource->getIterator(), true);
        } elseif ($dataSource instanceof Iterator) {
            $dataSource = iterator_to_array($dataSource, true);
        } elseif (! is_array($dataSource)) {
            throw new Exception(_('DataSource provided is not an array, nor does it implement Iterator or IteratorAggregate'));
        }
        if ($this->array_mask) {
            $columns = array_fill_keys($this->array_mask, null);
            $dataSource = array_intersect_key($dataSource, $columns);
        }
        $this->dataSource = new ArrayIterator($dataSource);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Bdts\Model\ObjectData.ObjectDataInterface::getData()
     */
    public function getArrayCopy()
    {
        return $this->getIterator() ? iterator_to_array($this->getIterator(), true) : array();
    }

    /**
     * Renvoie le nom de l'objet
     *
     * @return string
     */
    public function getObjName()
    {
        return $this->obj_name;
    }

    public function getIdFieldName()
    {
        return $this->id_field_name;
    }

    public function getId()
    {
        $data_array = $this->getArrayCopy();
        return $data_array[$this->getIdFieldName()];
    }

    /**
     * Renvoie les données sous la forme d'un Iterator
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->dataSource;
    }
}