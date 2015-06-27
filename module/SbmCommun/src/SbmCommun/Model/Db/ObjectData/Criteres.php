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
use Zend\View\Helper\Navigation\Breadcrumbs;

class Criteres implements ArraySerializableInterface
{

    /**
     * Tableau des données qui concernent le formulaire
     *
     * @var array
     */
    protected $data = array();

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
     * Renvoie la clause Where à appliquer au paginator.
     * 
     * Si on recherche dans une requête où le nom d'un champ se trouve dans plusieurs tables, il faut
     * préfixer le nom du champ. On utilise alors le tableau alias de la façon suivante:
     * Exemple: 'nomSA' => 'res.nomSA'
     * 
     * Si on recherche une expression, on on utilisera le tableau $alias en faisant précéder l'expression par 'Expression:'
     * Exemple: 'nbEnfants' => 'Expression:count(ele.eleveId) = ?'
     *
     * @param array $strict
     *            Tableau simple des champs pour lesquels on recherche l'égalité (les autres sont des likes)
     * @param array $alias
     *            Tableau associatif des champs dont il faut changer le nom, la clé n'étant qu'un alias
     *            
     * @return Zend\Db\Sql\Where
     *
     */
    public function getWhere($strict = array(), $alias = array())
    {
        $where = new Where();
        foreach ($this->data as $field => $value) {
            if (! empty($value) || (in_array($field, $strict) && $value == '0')) {
                $isExpression = false;
                $isLiteral = false;
                if (array_key_exists($field, $alias)) {
                    $field = $alias[$field];
                    $texpression = explode(':', $field);
                    if (count($texpression) == 2) {
                        $expression = $texpression[1];
                        switch (strtolower($texpression[0])) {
                            case 'literal':
                                $isLiteral = true;
                                break;
                            case 'expresssion':
                                $isExpression = true;
                                break;
                        }
                    }
                }
                if ($isLiteral) {
                    $where->literal($expression);
                } elseif ($isExpression) {
                    $where->expression($expression, $value);
                } elseif (in_array($field, $strict)) {
                    $where->equalTo($field, $value);
                } else {
                    $where->like($field, $value . '%');
                }
            }
        }
        return $where;
    }
}