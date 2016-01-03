<?php
/**
 * Méthodes communes aux classes de ce dossier
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource AbstractQuery.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 oct. 2015
 * @version 2015-1.6.5
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\Sql\Predicate\Not;

abstract class AbstractQuery
{
    /**
     * Transforme un tableau $filtre en Where
     *
     * Le tableau $filtre est composé des structures suivantes :<ul>
     * <li>$key => $value qui sera traduit en equalTo($key, $value)</li>
     * <li>'or' qui sera traduit en OR</li>
     * <li>'and' qui sera traduit en AND</li>
     * <li>'<' => array(left, right, [leftType, rightType]) qui sera traduit en lessThan</li>
     * <li>'>' => array(left, right, [leftType, rightType]) qui sera traduit en greaterThan</li>
     * <li>'=' => array(left, right, [leftType, rightType]) qui sera traduit en equalTo</li>
     * <li>'<=' => array(left, right, [leftType, rightType]) qui sera traduit en lessThanOrEqualTo</li>
     * <li>'>=' => array(left, right, [leftType, rightType]) qui sera traduit en greaterThanOrEqualTo</li>
     * <li>'<>' => array(left, right, [leftType, rightType]) qui sera traduit en notEqualTo</li>
     * <li>'sauf' => $sous_filtre qui sera traduit en NOT predicate où predicate est la transformation du $sous_filtre. On peut remplacer 'sauf' par 'not' ou 'pas'</li>
     * <li>$sous_filtre qui sera traduit en nest()->predicate->unnest() où predicate est la transformation du $sous_filtre</li></ul>
     *
     * @param Where $where
     * @param array $filtre
     *
     * @throws \InvalidArgumentException
     * @return Ambigous <Where, \Zend\Db\Sql\Where>
     */
    protected function arrayToWhere(Where $where = null, $filtre = array())
    {
        if (empty($where)) {
            $where = new Where();
        }
        foreach ($filtre as $key => $value) {
            if (is_array($value)) {
                $key = (string) $key;
                switch ($key) {
                    case 'not':
                    case 'sauf':
                    case 'pas':
                        $where->addPredicate(new Not($this->arrayToWhere(null, $value)));
                        break;
                    case '<':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->lessThan($value[0], $value[1]);
                                break;
                            case 4:
                                $where->lessThan($value[0], $value[1], $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException('Nombre incorrect de paramètres dans lessThan.');
                        }
                        break;
                    case '<=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->lessThanOrEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->lessThanOrEqualTo($value[0], $value[1], $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException('Nombre incorrect de paramètres dans lessThanOrEqualTo.');
                        }
                        break;
                    case '>':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->greaterThan($value[0], $value[1]);
                                break;
                            case 4:
                                $where->greaterThan($value[0], $value[1], $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException('Nombre incorrect de paramètres dans greaterThan.');
                        }
                        break;
                    case '>=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->greaterThanOrEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->greaterThanOrEqualTo($value[0], $value[1], $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException('Nombre incorrect de paramètres dans greaterThanOrEqualTo.');
                        }
                        break;
                    case '=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->equalTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->equalTo($value[0], $value[1], $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException('Nombre incorrect de paramètres dans notEqualTo.');
                        }
                        break;
                    case '<>':
                    case '!=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->notEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->notEqualTo($value[0], $value[1], $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException('Nombre incorrect de paramètres dans notEqualTo.');
                        }
                        break;
                    case 'isNull':
                    case 'is null':
                        $where->isNull($value[0]);
                        break;
                    case 'isNotNull':
                    case 'is not null':
                        $where->isNotNull($value[0]);
                        break;
                    default:
                        $where->nest()
                        ->addPredicate($this->arrayToWhere(null, $value))
                        ->unnest();
                        break;
                }
            } else {
                switch ($value) {
                    case 'or':
                        $where->or;
                        break;
                    case 'and':
                        $where->and;
                        break;
                    default:
                        $where->equalTo($key, $value);
                        break;
                }
            }
        }
        return $where;
    }    
}
 