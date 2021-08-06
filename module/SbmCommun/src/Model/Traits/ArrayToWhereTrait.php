<?php
/**
 * Transformation d'un tableau en Where
 *
 * @project sbm
 * @package SbmCommun/src/Model/Traits
 * @filesource ArrayToWhereTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmCommun\Model\Traits;

use SbmCommun\Model\Db\Sql\Predicate\Not;
use Zend\Db\Sql\Where;

trait ArrayToWhereTrait
{

    /**
     * Transforme un tableau $filtre en Where
     * Le tableau associatif $filtre est est interprété de la façon suivante :<ol>
     * <li>Si $value n'est pas un tableau, selon que la valeur de $value est : <ul>
     * <li>'or' : traduit en OR</li>
     * <li>'and' : traduit en AND</li>
     * <li> sinon : traduit en equalTo($key, $value)</li></ul></li>
     * <li>Si $value est un tableau on le considère comme un sous-filtre désigné par la
     * suite $sous_filtre. Selon que la valeur de $key est :<ul>
     * <li>'sauf' ou 'not' ou 'pas' => $sous_filtre : traduit en NOT predicate où
     * predicate est la transformation du $sous_filtre.</li>
     * <li>'<' => [left, right, [leftType, rightType]) : traduit en lessThan</li>
     * <li>'<=' => [left, right, [leftType, rightType]) : traduit en
     * lessThanOrEqualTo</li>
     * <li>'>' => [left, right, [leftType, rightType]) : traduit en greaterThan</li>
     * <li>'>=' => [left, right, [leftType, rightType]) : traduit en
     * greaterThanOrEqualTo</li>
     * <li>'=' => [left, right, [leftType, rightType]) : traduit en equalTo</li>
     * <li>'<>' ou '!=' => [left, right, [leftType, rightType]) qui sera traduit en
     * notEqualTo</li>
     * <li>dans tous les autres cas, $sous_filtre est traduit en
     * nest()->predicate->unnest() où predicate est
     * la transformation du $sous_filtre</li></ul></li></ol
     *
     * @param Where $where
     * @param array $filtre
     *
     * @throws \InvalidArgumentException
     * @return \Zend\Db\Sql\Where
     */
    protected function arrayToWhere(Where $where = null, $filtre = [])
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
                                $where->lessThan($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans lessThan.');
                        }
                        break;
                    case '<=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->lessThanOrEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->lessThanOrEqualTo($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans lessThanOrEqualTo.');
                        }
                        break;
                    case '>':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->greaterThan($value[0], $value[1]);
                                break;
                            case 4:
                                $where->greaterThan($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans greaterThan.');
                        }
                        break;
                    case '>=':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->greaterThanOrEqualTo($value[0], $value[1]);
                                break;
                            case 4:
                                $where->greaterThanOrEqualTo($value[0], $value[1],
                                    $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans greaterThanOrEqualTo.');
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
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans notEqualTo.');
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
                                $where->notEqualTo($value[0], $value[1], $value[2],
                                    $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans notEqualTo.');
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