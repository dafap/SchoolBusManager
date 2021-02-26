<?php
/**
 * Objet permettant d'enregistrer les résultats d'une opération sur une colonne.
 *
 * La propriété 'result' est un objet enregistrant le résultat par page, par groupe ou
 * pour tous les enregistrements
 *
 * La propriété 'pointer' tient à jour les pointeurs du lot de donnée :
 * - pointer->current : rang de l'enregistrement courant),
 * - pointer->pagebegin : rang de l'enregistrement de début de page)
 * - pointer->groupbegins[$key] : rang de l'enregistrement de début du groupe $key
 * Les pointeurs servent à paramétrer la méthode Range() de la classe Calcul.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Element
 * @filesource ProcessFeatures.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 févr. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Element;

use SbmBase\Model\StdLib;

class ProcessFeatures
{
    use \SbmCommun\Model\Traits\DebugTrait;

    private $operator;

    private $column;

    private $group;

    private $result;

    private $pointer;

    /**
     * <ul>
     * <li><b>operator</b> reçoit un ou plusieurs operators (voir méthode
     * getListOperators())</li>
     * <li><b>column</b> est disponible pour enregistrer des infos sur une ou plusieurs
     * colonnes</li>
     * <li><b>group</b> est un objet dont les propriétés sont :<ul>
     * <li><b>rowIdx</b> : tableau des index dans row des colonnes pour lesquelles on a
     * l'attribut 'nl' dans la table columns</li>
     * <li><b>valueRowIdx</b> : valeur actuelle de row[rowIdx] correspond à une constante
     * du groupe</li></ul>
     * <li><b>result</b> enregistre les résultats des calculs pour la page, les groupes,
     * le tout</li>
     * <li><b>pointer</b> enregistre le pointeur courant dans rowset et les pointeurs de
     * début de page ou de début de groupe</li></ul>
     *
     * @param mixed $operator
     */
    public function __construct($operator = null)
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'),
            'tablesimple.log');
        $this->debugLog(__METHOD__);
        $this->operator = $operator;
        $this->column = null;
        $this->group = (object) [
            'rowIdx' => [],
            'valueRowIdx' => []
        ];
        $this->result = (object) [
            'page' => null,
            'groups' => [],
            'all' => null
        ];
        $this->pointer = (object) [
            'current' => 0,
            'pagebegin' => 0,
            'groupbegins' => []
        ];
    }

    public function getListOperators()
    {
        $this->debugLog(__METHOD__);
        return [
            'sum' => 'Somme',
            'ave' => 'Moyenne',
            'min' => 'Min',
            'max' => 'Max',
            'rec' => 'Compter les enregistrements',
            'val' => 'Compter les valeurs',
            'dev' => 'Ecart type',
            'var' => 'Variance',
            'rep' => 'Répartition',
            'q1' => 'Quartile 1',
            'med' => 'Médiane',
            'q3' => 'Quartile 3'
        ];
    }

    public function getOperator()
    {
        $this->debugLog(__METHOD__);
        return $this->operator;
    }

    public function getColumn()
    {
        $this->debugLog(__METHOD__);
        return $this->column;
    }

    public function setOperator($operator)
    {
        $this->debugLog(__METHOD__);
        $this->operator = $operator;
        return $this;
    }

    public function setColumn($column)
    {
        $this->debugLog(__METHOD__);
        $this->column = $column;
        return $this;
    }

    public function addGroup($key)
    {
        $this->debugLog(__METHOD__);
        $this->group->rowIdx[] = $key;
        $i = count($this->group->rowIdx) - 1;
        $this->group->valueRowIdx[$i] = null;
        $this->pointer->groubegins[$key] = 0;
        $this->result->groups[$key] = null;
        return $this;
    }

    public function initGroup($columns)
    {
        $this->debugLog(__METHOD__);
        for ($i = 0; $i < count($columns); $i ++) {
            if (StdLib::getParamR([
                $i,
                'nl'
            ], $columns, false)) {
                $this->addGroup($i);
            }
        }
    }

    public function setGroupValue($key, $value)
    {
        $this->debugLog(__METHOD__);
        $this->group->valueRowIdx[$key] = $value;
        return $this;
    }

    /**
     * On donne une ligne du tableau pour vérifier si les valeurs des colonnes de group
     * ont changées.
     * Si c'est le cas on renvoie <b>true</b> sinon on renvoie <b>false</b>.
     *
     * @param array $row
     * @return boolean
     */
    public function isNewGroup($row)
    {
        $this->debugLog(__METHOD__);
        $newGroup = false;
        foreach ($this->group->rowIdx as $idx) {
            if (! empty($this->group->valueRowIdx[$idx]) &&
                $this->group->valueRowIdx[$idx] != $row[$idx]) {
                $newGroup = true;
                break;
            }
        }
        return $newGroup;
    }

    /**
     * Enregistrement des nouvelles valeurs des pointers et colonnes de group
     *
     * @param array $row
     * @return \SbmPdf\Model\Element\ProcessFeatures
     */
    public function newGroup($row)
    {
        $this->debugLog(__METHOD__);
        foreach ($this->group->rowIdx as $idx) {
            if (! empty($this->group->valueRowIdx[$idx]) &&
                $this->group->valueRowIdx[$idx] != $row[$idx]) {
                $this->pointer->groupbegins[$idx] = $this->pointer->current;
            }
            $this->setGroupValue($idx, $row[$idx]);
        }

        return $this;
    }

    public function reset()
    {
        $this->debugLog(__METHOD__);
        $this->pointer->current = 0;
        $this->newPage();
        foreach (array_keys($this->pointer->groupbegins) as $key) {
            $this->newGroup($key);
        }
        return $this;
    }

    public function nextPointer()
    {
        $this->debugLog(__METHOD__);
        $this->pointer->current ++;
        return $this;
    }

    public function newPage()
    {
        $this->debugLog(__METHOD__);
        $this->pointer->pagebegin = $this->pointer->current;
        return $this;
    }

    public function getPointerCurrent()
    {
        $this->debugLog(__METHOD__);
        return $this->pointer->current;
    }

    public function getPointerPageBegin()
    {
        $this->debugLog(__METHOD__);
        return $this->pointer->pagebegin;
    }

    public function getPointerGroupBegin($key)
    {
        $this->debugLog(__METHOD__);
        return $this->pointer->groupbegins[$key];
    }

    public function getResultPage()
    {
        $this->debugLog(__METHOD__);
        return $this->result->page;
    }

    public function getResultGroup($key)
    {
        $this->debugLog(__METHOD__);
        return $this->result->group[$key];
    }

    public function getResultAll()
    {
        $this->debugLog(__METHOD__);
        return $this->result->all;
    }
}