<?php
/**
 * Service fournissant une liste des classes sous la forme d'un tableau
 *   'classeId' => 'nom'
 * 
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource ClassesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2018
 * @version 2018-2.4.1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Strategy\Niveau;
use SbmCommun\Filter\MbUcfirst;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class ClassesForSelect implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     *
     * @var string
     */
    private $table_name;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->table_name = $this->db_manager->getCanonicName('classes', 'table');
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        return $this;
    }

    public function tout()
    {
        $mbUcfirst = new MbUcfirst();
        $select = $this->sql->select($this->table_name);
        $select->columns(
            [
                'classeId',
                'nom',
                'niveau'
            ]);
        $select->order(
            [
                'niveau',
                'rang',
                'nom'
            ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            if (array_key_exists($row['niveau'], $array)) {
                $array[$row['niveau']]['options'][$row['classeId']] = $row['nom'];
            } else {
                $array[$row['niveau']] = [
                    'label' => $mbUcfirst->filter(Niveau::getNiveaux()[$row['niveau']]),
                    'options' => [
                        $row['classeId'] => $row['nom']
                    ]
                ];
            }
        }
        return $array;
    }

    /**
     *
     * @param string $op
     *            mots parmis '=', '==', '<', '<=', '>', '>=', '<>', 'in', 'between'
     * @param mixed $params
     *            int ou array, selon op
     * @throws Exception
     * @return Ambigous <multitype:multitype:multitype:unknown mixed , unknown>
     */
    public function niveau($params, $op = '=')
    {
        $where = new Where();
        switch (strtolower($op)) {
            case '<':
                if (! is_scalar($params)) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->lessThan('niveau', $params);
                break;
            case '<=':
                if (! is_scalar($params)) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->lessThanOrEqualTo('niveau', $params);
                break;
            case '>':
                if (! is_scalar($params)) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->greaterThan('niveau', $params);
                break;
            case '>=':
                if (! is_scalar($params)) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->greaterThanOrEqualTo('niveau', $params);
                break;
            case '!=':
            case '<>':
                if (! is_scalar($params)) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->notEqualTo('niveau', $params);
                break;
            case 'in':
                if (! is_array($params)) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->in('niveau', $params);
                break;
            case 'not in':
            case 'not_in':
            case 'not-in':
            case 'notin':
                if (! is_array($params)) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->notIn('niveau', $params);
                break;
            case 'between':
                if (! is_array($params) || count($params) != 2) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->between('niveau', min($params), max($params));
                break;
            case 'not between':
            case 'not_between':
            case 'not-between':
            case 'notbetween':
                if (! is_array($params) || count($params) != 2) {
                    throw new \InvalidArgumentException(
                        'Argument invalide pour cet opérateur.');
                }
                $where->notIn('niveau', $params);
                break;
            case '=':
            case '==':
            default:
                $where->equalTo('niveau', $params);
                break;
        }
        $mbUcfirst = new MbUcfirst();
        $select = $this->sql->select($this->table_name)
            ->columns(
            [
                'classeId',
                'nom',
                'niveau'
            ])
            ->where($where)
            ->order(
            [
                'niveau',
                'rang',
                'nom'
            ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = [];
        foreach ($rowset as $row) {
            if (array_key_exists($row['niveau'], $array)) {
                $array[$row['niveau']]['options'][$row['classeId']] = $row['nom'];
            } else {
                $array[$row['niveau']] = [
                    'label' => $mbUcfirst->filter(Niveau::getNiveaux()[$row['niveau']]),
                    'options' => [
                        $row['classeId'] => $row['nom']
                    ]
                ];
            }
        }
        return $array;
    }
}