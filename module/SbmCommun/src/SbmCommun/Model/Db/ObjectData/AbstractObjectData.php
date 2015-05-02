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
     * @var array Iterator IteratorAggregate
     */
    protected $dataSource = array();

    /**
     * Nom de l'objet à initialiser dans le constructeur
     *
     * @var string
     */
    private $obj_name;

    /**
     * Nom(s) du (ou des) champ(s) id
     * (voir la définition de primary key pour la table concernée)
     *
     * @var string|array
     */
    private $id_field_name;

    /**
     * Masque servant de modèle pour la composition de la donnée dans la méthode exchangeArray()
     * (Pour un objet associé à une table, c'est la liste des noms de colonnes)
     *
     * @var array
     */
    private $array_mask = array();

    /**
     * Tableau des champs qui doivent être null lorsqu'ils sont vides
     * Ce tableau est de la forme array('field_name' => boolean, .
     * ..)
     * où boolean est vrai si le champ doit être null lorsqu'il est vide
     * pour tous les champs de l'objectdata
     *
     * @var array
     */
    private $are_nullable = array();

    /**
     * Liste des champs calculés à mettre à jour par l'hydrator
     *
     * @var array of string
     */
    private $calculate_fields = array();

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
                throw new Exception(sprintf('Impossible de trouver la propriété %s.<pre>%s</pre>', $param, print_r($this->dataSource, true)));
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
     * Affectation par défaut
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
     * @param string|array $name            
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
     * Enregistre la liste des champs avec l'indicateur is_nullable (boolean)
     *
     * @param array $are_nullable            
     * @throws Exception
     */
    public function setAreNullable($are_nullable = array())
    {
        if (! is_array($are_nullable)) {
            throw new Exception(__METHOD__ . _(' - AreNullable provided is not an array'));
        }
        $this->are_nullable = $are_nullable;
    }

    /**
     * Renvoie le masque utilisé dans exchangeArray()
     *
     * @return array
     */
    public function getArrayMask()
    {
        return $this->array_mask;
    }

    /**
     * Initialise la propriété calculate_fields avec le tableau fourni
     *
     * @param array $array            
     */
    public function setCalculateFields($array)
    {
        if (! is_array($array)) {
            ob_start();
            var_dump($array);
            $dump = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception(__METHOD__ . " Le paramètre fourni doit être un tableau. On a reçu :\n$dump");
        }
        $this->calculate_fields = $array;
    }

    /**
     * Ajoute le champ fourni comme paramètre dans la propriété calculate_fields
     *
     * @param string $str            
     */
    public function addCalculateField($str)
    {
        if (! is_string($str)) {
            ob_start();
            var_dump($str);
            $dump = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception(__METHOD__ . " Le paramètre fourni doit être une chaîne de caractère. On a reçu :\n$dump");
        }
        $this->calculate_fields[] = $str;
    }

    /**
     * Renvoie le tableau des champs calculés
     *
     * @return array
     */
    public function getCalculateFields()
    {
        return $this->calculate_fields;
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
        foreach ($this->are_nullable as $key => $is_nullable) {
            if (!array_key_exists($key, $dataSource)) continue;
            if ($is_nullable) {
                if (empty($dataSource[$key])) {
                    $dataSource[$key] = null;
                }
            } else {
                if (is_null($dataSource[$key])) {
                    unset($dataSource[$key]);
                }
            }
        }
        $this->dataSource = new ArrayIterator($dataSource);
        return $this;
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

    /**
     * Devra être surchargé si un contrôle de construction d'id est nécessaire.
     * C'est le cas lorsque la primary key est composée de plusieurs champs.
     *
     * @param int|string|array $id            
     * @return boolean
     */
    public function isValidId($id)
    {
        return is_null($id) ? false : true;
    }

    /**
     * Renvoie un id correct : scalaire si id_field_name est un scalaire, tableau associatif si id_field_name est un tableau
     * On s'assurera avant que l'id est valide
     *
     * @param int|string|array $id            
     *
     * @return int|string|array
     */
    public function getValidId($id)
    {
        if (is_array($this->id_field_name)) {
            if (is_string($id)) {
                $parts = explode('|', $id);
                $result = array();
                for ($i = 0; $i < count($this->id_field_name); $i ++) {
                    $result[$this->id_field_name[$i]] = $parts[$i];
                }
                return $result;
            }
        }
        return $id;
    }

    public function getId()
    {
        $data_array = $this->getArrayCopy();
        if (is_array($this->getIdFieldName())) {
            $id = array();
            foreach ($this->getIdFieldName() as $item) {
                $id[$item] = $data_array[$item];
            }
            return $id;
        } else {
            return $data_array[$this->getIdFieldName()];
        }
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

    /**
     * Indique si l'objet courant a changé par rapport à l'ancien objet passé en paramètre
     *
     * @param ObjectDataInterface $old_obj            
     *
     * @return boolean
     */
    public function isUnchanged(ObjectDataInterface $old_obj)
    {
        $data1 = $this->getArrayCopy();
        $data2 = $old_obj->getArrayCopy();
        $commun1 = array_intersect_key($data1, $data2);
        $commun2 = array_intersect_key($data2, $data1);
        return $commun1 == $commun2;
    }
}