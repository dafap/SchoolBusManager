<?php
/**
 * Méthodes communes aux classes de ce dossier
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource AbstractQuery.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 août 2021
 * @version 2021-2.6.3
 */
namespace SbmGestion\Model\Db\Service;

use SbmCommun\Model\Db\Sql\Predicate\Not;
use Zend\Db\Sql\Where;

abstract class AbstractQuery
{
    use \SbmCommun\Model\Traits\SqlStringTrait;

    /**
     * Transforme un tableau $filtre en Where Le tableau $filtre est composé des
     * structures suivantes :<ul> <li>$key => $value qui sera traduit en equalTo($key,
     * $value)</li> <li>'or' qui sera traduit en OR</li> <li>'and' qui sera traduit en
     * AND</li> <li>'<' => [left, right, [leftType, rightType]) qui sera traduit en
     * lessThan</li> <li>'>' => [left, right, [leftType, rightType]) qui sera traduit en
     * greaterThan</li> <li>'=' => [left, right, [leftType, rightType]) qui sera traduit
     * en equalTo</li> <li>'<=' => [left, right, [leftType, rightType]) qui sera traduit
     * en lessThanOrEqualTo</li> <li>'>=' => [left, right, [leftType, rightType]) qui sera
     * traduit en greaterThanOrEqualTo</li> <li>'<>' => [left, right, [leftType,
     * rightType]) qui sera traduit en notEqualTo</li> <li>'sauf' => $sous_filtre qui sera
     * traduit en NOT predicate où predicate est la transformation du $sous_filtre. On
     * peut remplacer 'sauf' par 'not' ou 'pas'</li> <li>$sous_filtre qui sera traduit en
     * nest()->predicate->unnest() où predicate est la transformation du
     * $sous_filtre</li></ul>
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
                $key = strtolower((string) $key);
                switch ($key) {
                    case 'if':
                        if (count($value) == 3) {
                            $condition = $this->literal($value[0]);
                            $expression1 = $this->literal($value[1]);
                            $expression2 = $this->literal($value[2]);
                            $where->literal("IF($condition,$expression1,$expression2)");
                        } else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans if.');
                        }
                        break;
                    case 'in':
                        $nbParameters = count($value);
                        if ($nbParameters == 2)
                            if (is_array($value[1])) {
                                $where->in($this->literal($value[0]), $value[1]);
                            } else {
                                throw new \InvalidArgumentException(
                                    'Deuxième paramètre incorrect dans in.');
                            }
                        else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans in.');
                        }
                        break;
                    case 'notin':
                    case 'not in':
                        $nbParameters = count($value);
                        if ($nbParameters == 2)
                            if (is_array($value[1])) {
                                $where->notIn($this->literal($value[0]), $value[1]);
                            } else {
                                throw new \InvalidArgumentException(
                                    'Deuxième paramètre incorrect dans notIn.');
                            }
                        else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans notIn.');
                        }
                        break;
                    case 'not':
                    case 'sauf':
                    case 'pas':
                        $where->addPredicate(new Not($this->arrayToWhere(null, $value)));
                        break;
                    case '<':
                        $nbParameters = count($value);
                        switch ($nbParameters) {
                            case 2:
                                $where->lessThan($value[0], $this->literal($value[1]));
                                break;
                            case 4:
                                $where->lessThan($this->literal($value[0]),
                                    $this->literal($value[1]), $value[2], $value[3]);
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
                                $where->lessThanOrEqualTo($value[0],
                                    $this->literal($value[1]));
                                break;
                            case 4:
                                $where->lessThanOrEqualTo($this->literal($value[0]),
                                    $this->literal($value[1]), $value[2], $value[3]);
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
                                $where->greaterThan($value[0], $this->literal($value[1]));
                                break;
                            case 4:
                                $where->greaterThan($this->literal($value[0]),
                                    $this->literal($value[1]), $value[2], $value[3]);
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
                                $where->greaterThanOrEqualTo($value[0],
                                    $this->literal($value[1]));
                                break;
                            case 4:
                                $where->greaterThanOrEqualTo($this->literal($value[0]),
                                    $this->literal($value[1]), $value[2], $value[3]);
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
                                $where->equalTo($value[0], $this->literal($value[1]));
                                break;
                            case 4:
                                $where->equalTo($this->literal($value[0]),
                                    $this->literal($value[1]), $value[2], $value[3]);
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
                                $where->notEqualTo($value[0], $this->literal($value[1]));
                                break;
                            case 4:
                                $where->notEqualTo($this->literal($value[0]),
                                    $this->literal($value[1]), $value[2], $value[3]);
                                break;
                            default:
                                throw new \InvalidArgumentException(
                                    'Nombre incorrect de paramètres dans notEqualTo.');
                        }
                        break;
                    case 'between':
                        $nbParameters = count($value);
                        if ($nbParameters == 3) {
                            $where->between($value[0], $this->literal($value[1]),
                                $this->literal($value[2]));
                        } else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans between.');
                        }
                        break;
                    case 'notbetween':
                    case 'not between':
                        $nbParameters = count($value);
                        if ($nbParameters == 3) {
                            $where->notBetween($value[0], $this->literal($value[1]),
                                $this->literal($value[2]));
                        } else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans notBetween.');
                        }
                        break;
                    case 'isnull':
                    case 'is null':
                        $where->isNull($this->literal($value));
                        break;
                    case 'isnotnull':
                    case 'is not null':
                        $where->isNotNull($this->literal($value));
                        break;
                    case 'like':
                        $nbParameters = count($value);
                        if ($nbParameters == 2) {
                            $where->like($value[0], $this->literal($value[1]));
                        } else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans like.');
                        }
                        break;
                    case 'notlike':
                    case 'not like':
                        $nbParameters = count($value);
                        if ($nbParameters == 2) {
                            $where->notLike($value[0], $this->literal($value[1]));
                        } else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans notLike.');
                        }
                        break;
                    case 'literal':
                        $where->literal($this->literal($value));
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

    protected function literal($array)
    {
        if (is_string($array) || is_numeric($array)) {
            return $array;
        } elseif (is_array($array)) {
            $literal = '';
            foreach ($array as $key => $part) {
                $key = strtolower((string) $key);
                switch ($key) {
                    case 'and':
                    case 'or':
                        if (! is_array($part) || empty($part)) {
                            throw new \InvalidArgumentException(
                                'Opération logiquen demandée. Arguments inccorrects : On attend un tableau de valeurs.');
                        }
                        $expression = '(' . $this->literal($part[0]);
                        $key = strtoupper($key);
                        for ($idx = 1; $idx < count($part); $idx ++) {
                            $expression .= " $key " . $this->literal($part[$idx]);
                        }
                        $expression .= ')';
                        $literal .= $expression;
                        break;
                    case 'isnull':
                    case 'is null':
                        $literal .= sprintf('ISNULL(%s)', $this->literal($part));
                        break;
                    case 'isnotnull':
                    case 'is notnull':
                    case 'is not null':
                        $literal .= sprintf('NOT ISNULL(%s)', $this->literal($part));
                        break;
                    case '-':
                        if (is_string($part) || is_numeric($part)) {
                            $literal .= '- ' . $part;
                            break;
                        } elseif (is_array($part) && count($part) == 1) {
                            $literal .= '(-' . $this->literal($part) . ')';
                            break;
                        }
                    case '+':
                    case '*':
                    case '/':
                    case '%':
                    case 'div':
                    case 'mod':
                        if (! is_array($part) || empty($part)) {
                            throw new \InvalidArgumentException(
                                'Opération demandée. Arguments inccorrects : On attend un tableau de valeurs.');
                        }
                        $key = strtoupper($key);
                        $expression = $this->literal($part[0]);
                        for ($idx = 1; $idx < count($part); $idx ++) {
                            $expression = '(' . $expression . " $key " .
                                $this->literal($part[$idx]) . ')';
                        }
                        $literal .= $expression;
                        break;
                    case '~':
                        $literal .= '(~' . $this->literal($part) . ')';
                        break;
                    case '&':
                    case '|':
                    case '^':
                    case '<<':
                    case '>>':
                        if (! is_array($part) && count($part) != 2) {
                            throw new \InvalidArgumentException(
                                'Opération binaire demandée. Arguments inccorrects : On attend un tableau de 2 valeurs.');
                        }
                        $literal .= '(' . $this->literal($part[0]) . " $key " .
                            $this->literal($part[1]) . ')';
                        break;
                    case '<':
                    case '<=':
                    case '>':
                    case '>=':
                    case '=':
                    case '<>':
                        if (! is_array($part) || count($part) != 2) {
                            throw new \InvalidArgumentException(
                                'Comparaison demandée. Arguments inccorrects : On attend un tableau de 2 valeurs.');
                        }
                        $literal .= '(' . $this->literal($part[0]) . " $key " .
                            $this->literal($part[1]) . ')';
                        break;
                    case 'in':
                        $nbParameters = count($part);
                        if ($nbParameters == 2)
                            if (is_array($part[1])) {
                                $literal .= '(' . $this->literal($part[0]) . ' IN ' .
                                    $this->arrayToString($part[1]) . ')';
                            } else {
                                throw new \InvalidArgumentException(
                                    'Deuxième paramètre incorrect dans IN.');
                            }
                        else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans IN.');
                        }
                        break;
                    case 'notin':
                    case 'not in':
                        $nbParameters = count($part);
                        if ($nbParameters == 2)
                            if (is_array($part[1])) {
                                $literal .= '(' . $this->literal($part[0]) . ' NOT IN ' .
                                    $this->arrayToString($part[1]) . ')';
                            } else {
                                throw new \InvalidArgumentException(
                                    'Deuxième paramètre incorrect dans NOT IN.');
                            }
                        else {
                            throw new \InvalidArgumentException(
                                'Nombre incorrect de paramètres dans NOT IN.');
                        }
                        break;
                }
            }
            return $literal;
        } else {
            throw new \InvalidArgumentException('Argument incorrect pour un literal');
        }
    }

    private function arrayToString(array $array): string
    {
        $result = '(' . $this->literal($array[0]);
        for ($idx = 1; $idx < count($array); $idx ++) {
            $result .= ', ' . $this->literal($array[$idx]);
        }
        return $result . ')';
    }
}
