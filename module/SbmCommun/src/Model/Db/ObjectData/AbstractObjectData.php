<?php
/**
 * Classe abstraite pour définir les objets data à manipuler dans les tables et les formulaires
 *
 * (test phpunit complet)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ObjectData
 * @filesource AbstractObjectData.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\ObjectData;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

abstract class AbstractObjectData implements ObjectDataInterface, \Countable
{

    const ERROR_PROPERTY_MSG = 'Unable to find property %s.<pre>%s</pre>';

    const ERROR_NOT_ARRAY = '%s - The expected parameter must be an array. It provided :<pre>%s</pre>.';

    const ERROR_NOT_STRING = '%s - The expected parameter must be a string. It provided :<pre>%s</pre>.';

    const ERROR_EXCHANGE = '%s - DataSource provided is not an array, nor does it implement Iterator or IteratorAggregate. It provided :<pre>%s</pre>.';

    /**
     * Données de l'objet
     *
     * @var array Iterator IteratorAggregate
     */
    protected $dataSource = [];

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
    private $array_mask = [];

    /**
     * Tableau des champs qui doivent être null lorsqu'ils sont vides
     * Ce tableau est de la forme ['field_name' => boolean,)
     * où boolean est vrai si le champ doit être null lorsqu'il est vide
     * pour tous les champs de l'objectdata
     *
     * @var array
     */
    private $are_nullable = [];

    /**
     * Liste des champs calculés à mettre à jour par l'hydrator
     *
     * @var array of string
     */
    private $calculate_fields = [];

    /**
     * Renvoie la donnée correspondant à la propriété indiquée.
     * Lance une exception si le paramètre n'est pas présent dans dataSource.
     *
     * @param string $param
     *            nom de la propriété
     *            
     * @throws Exception
     *
     * @return mixed
     */
    public function __get($param)
    {
        if ($this->dataSource instanceof \ArrayIterator) {
            if ($this->dataSource->offsetExists($param)) {
                return $this->dataSource->offsetGet($param);
            } else {
                throw new Exception(
                    sprintf(_(self::ERROR_PROPERTY_MSG), $param,
                        print_r($this->dataSource, true)));
            }
        } else {
            foreach ($this->dataSource as $key => $value) {
                if ($param == $key)
                    return $value;
            }
            throw new Exception(
                sprintf(_(self::ERROR_PROPERTY_MSG), $param,
                    print_r($this->dataSource, true)));
        }
    }

    /**
     * Affectation par défaut.
     * Lance une exception si la propriété n'existe pas et n'est pas autorisée
     * dans array_mask.
     *
     * @param string $param
     *            nom de la propriété
     * @param mixed $valeur
     *
     * @throws Exception
     */
    public function __set($param, $valeur)
    {
        if ($this->dataSource instanceof \ArrayIterator) {
            if ($this->dataSource->offsetExists($param) ||
                in_array($param, $this->array_mask)) {
                $this->dataSource->offsetSet($param, $valeur);
            } else {
                throw new Exception(
                    sprintf(_(self::ERROR_PROPERTY_MSG), $param,
                        print_r($this->array_mask, true)));
            }
        } elseif (is_array($this->dataSource) && in_array($param, $this->array_mask)) {
            $this->dataSource[$param] = $valeur;
        } else {
            // sinon, on ne met à jour que les paramètres existants
            foreach ($this->dataSource as $key => &$value) {
                if ($param == $key) {
                    $value = $valeur;
                    return;
                }
            }
            unset($value);
            throw new Exception(
                sprintf(_(self::ERROR_PROPERTY_MSG), $param,
                    print_r($this->dataSource, true)));
        }
    }

    /**
     * Cette méthode magique est appelée chaque fois que isset() est appelée sur une propriété
     * de la variable d'objet.
     *
     * @param string $param
     *            nom du paramètre
     *            
     * @return bool vrai si la variable d'objet est défini, sinon false
     */
    public function __isset($param)
    {
        if ($this->dataSource instanceof \ArrayIterator) {
            return $this->dataSource->offsetExists($param);
        } elseif (is_array($this->dataSource)) {
            return isset($this->dataSource[$param]);
        } else {
            foreach ($this->dataSource as $key => $value) {
                if ($param == $key) {
                    return true;
                }
            }
            unset($value);
        }
        return false;
    }

    /**
     * Cette méthode magique est appelée chaque fois que unset() est appelé sur une propriété
     * de la variable d'objet.
     *
     * @param string $param
     */
    public function __unset($param)
    {
        if ($this->dataSource instanceof \ArrayIterator &&
            $this->dataSource->offsetExists($param)) {
            return $this->dataSource->offsetUnset($param);
        } elseif (is_array($this->dataSource)) {
            unset($this->dataSource[$param]);
        } elseif ($this->dataSource instanceof \IteratorAggregate) {
            unset($this->dataSource->$param);
        }
    }

    /**
     * Nombre d'éléments définis dans la propriété dataSource
     *
     * @see \Countable::count()
     *
     * @return int
     */
    public function count()
    {
        if ($this->dataSource instanceof \ArrayIterator) {
            return $this->dataSource->count();
        } elseif (is_array($this->dataSource)) {
            return count($this->dataSource);
        } else {
            $count = 0;
            foreach ($this->dataSource->getIterator() as $value) {
                $count ++;
            }
            unset($value);
            return $count;
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
     * Enregistre le(s) nom(s) du(des) champ(s) Id de l'objet.
     * Peut être une chaine de caractères ou un tableau.
     *
     * @param string|array $name
     * @return void
     */
    protected function setIdFieldName($name)
    {
        $this->id_field_name = $name;
    }

    /**
     * Enregistre le masque à utiliser pour la méthode exchangeArray().
     * Lance une exception si le paramètre n'est pas un tableau.
     *
     * @param array $array_mask
     *
     * @throws Exception
     * @see Bdts\Model\ObjectData.ObjectDataInterface::setArrayMask()
     */
    public function setArrayMask($array_mask = [])
    {
        if (! is_array($array_mask)) {
            throw new Exception(
                sprintf(_(self::ERROR_NOT_ARRAY), __METHOD__, gettype($array_mask)));
        }
        $this->array_mask = $array_mask;
    }

    /**
     * Enregistre la liste des champs avec l'indicateur is_nullable (boolean)
     * Lance une exception si le paramètre n'est pas un tableau.
     *
     * @param array $are_nullable
     * @throws Exception
     */
    public function setAreNullable($are_nullable = [])
    {
        if (! is_array($are_nullable)) {
            throw new Exception(
                sprintf(_(self::ERROR_NOT_ARRAY), __METHOD__, gettype($are_nullable)));
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
     * Initialise la propriété calculate_fields avec le tableau fourni.
     * Lance une exception si le paramètre n'est pas un tableau.
     *
     * @param array $array
     *
     * @throws Exception
     */
    public function setCalculateFields($array)
    {
        if (! is_array($array)) {
            ob_start();
            var_dump($array);
            $dump = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception(sprintf(_(self::ERROR_NOT_ARRAY), __METHOD__, $dump));
        }
        $this->calculate_fields = $array;
    }

    /**
     * Ajoute le champ fourni comme paramètre dans la propriété calculate_fields
     * Lance une exception si le paramètre n'est pas une chaine de caractères.
     *
     * @param string $str
     *
     * @throws Exception
     */
    public function addCalculateField($str)
    {
        if (! is_string($str)) {
            ob_start();
            var_dump($str);
            $dump = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception(sprintf(_(self::ERROR_NOT_STRING), __METHOD__, $dump));
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
     * Lance une exception si le paramètre n'est pas du type attendu.
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
            ob_start();
            var_dump($dataSource);
            $dump = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception(sprintf(_(self::ERROR_EXCHANGE), __METHOD__, $dump));
        }
        if ($this->array_mask) {
            $columns = array_fill_keys($this->array_mask, null);
            $dataSource = array_intersect_key($dataSource, $columns);
        }
        foreach ($this->are_nullable as $key => $is_nullable) {
            if (! array_key_exists($key, $dataSource))
                continue;
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
        if (is_array($this->dataSource)) {
            return $this->dataSource;
        } elseif ($this->dataSource instanceof \ArrayIterator) {
            return $this->dataSource->getArrayCopy();
        }
        return $this->getIterator() ? iterator_to_array($this->getIterator(), true) : [];
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

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\ObjectData\ObjectDataInterface::getIdFieldName()
     */
    public function getIdFieldName()
    {
        return $this->id_field_name;
    }

    /**
     * Devra être surchargé si un contrôle de construction d'id est nécessaire.
     * C'est le cas lorsque la primary key est composée de plusieurs champs.
     *
     * @param int|string|array $id
     *
     * @return boolean
     */
    public function isValidId($id)
    {
        return is_null($id) ? false : true;
    }

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
    public function getValidId($id)
    {
        if (is_array($this->id_field_name)) {
            if (is_string($id)) {
                $parts = explode('|', $id);
                $result = [];
                for ($i = 0; $i < count($this->id_field_name); $i ++) {
                    $result[$this->id_field_name[$i]] = $parts[$i];
                }
                return $result;
            }
        }
        return $id;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\ObjectData\ObjectDataInterface::getId()
     */
    public function getId()
    {
        $data_array = $this->getArrayCopy();
        if (is_array($this->getIdFieldName())) {
            $id = [];
            foreach ($this->getIdFieldName() as $item) {
                $id[$item] = $data_array[$item];
            }
            return $id;
        } else {
            if (array_key_exists($this->getIdFieldName(), $data_array)) {
                return $data_array[$this->getIdFieldName()];
            } else {
                return false;
            }
        }
    }

    /**
     * Renvoie les données sous la forme d'un Iterator
     *
     * @return Iterator
     */
    public function getIterator()
    {
        if (is_array($this->dataSource)) {
            return new \ArrayIterator($this->dataSource);
        }
        if ($this->dataSource instanceof \IteratorAggregate) {
            return $this->dataSource->getIterator();
        }
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