<?php
/**
 * ObjectData pour échange de données avec CriteresForm
 * (multi tables)
 *
 * @project sbm
 * @package SbmCommun\Form\ObjectData
 * @filesource Criteres.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ObjectData;

use Zend\Stdlib\ArraySerializableInterface;
use Zend\Db\Sql\Where;

class Criteres implements ArraySerializableInterface
{

    /**
     * Tableau des données qui concernent le formulaire
     *
     * @var array
     */
    protected  $data = array();

    /**
     * Nom réel de la table dans la base de donnée
     *
     * @var string
     */
    private $table;

    /**
     * Constructeur
     *
     * @param string $table_name
     *            Le nom réel de la table dans la base de données
     * @param array $form_fields
     *            Tableau des noms de champs du formulaire
     */
    public function __construct($form_fields)
    {
        $this->createDataStructure($form_fields);
    }

    public function exchangeArray(array $array)
    {
        $this->data = array_merge($this->data, array_intersect_key($array, $this->data));
    }

    public function getArrayCopy()
    {
        return $this->data;
    }

    /**
     * Prépare la structure de propriété data
     * 
     * @param array $fields            
     */
    public function createDataStructure($fields)
    {
        if (! is_array($fields)) {
            throw new Exception(sprintf("Tableau attendu. On a reçu un %s", gettype($fields)));
        }
        $this->data = array_fill_keys($fields, null);
    }

    /**
     * Renvoie la clause Where à appliquer au paginator
     *
     * @return Zend\Db\Sql\Where
     *
     */
    public function getWhere($strict = array())
    {
        $where = new Where();
        foreach ($this->data as $field => $value) {
            if (! empty($value) || (in_array($field, $strict) && $value == '0')) {
                if (in_array($field, $strict)) {
                    $where->equalTo($field, $value);
                } else {
                    $where->like($field, $value . '%');
                }
            }
        }
        return $where;
    }
}