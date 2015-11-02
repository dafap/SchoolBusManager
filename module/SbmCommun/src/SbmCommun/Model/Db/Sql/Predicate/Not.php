<?php
/**
 * Complément à ZF2 - Predicate Not
 * 
 * ==============================================
 * Usage :
 * $where = new Where();
 * $where->notIn('id', array(1, 2));
 * $where->equalTo('name', 'Foo');
 * 
 * $select = new Select('tbl_');
 * $select->where->addPredicate(new Not($where));
 * 
 * var_dump($select->getSqlString());
 * 
 * Output :
 * string 'SELECT "tbl_".* FROM "tbl_" WHERE NOT ("id" NOT IN ('1', '2') AND "name" = 'Foo')' (length=81)
 * ==============================================
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource Not.php
 * @encodage UTF-8
 * @author PhpStorm - Exlord (adeli.farhad@gmail.com)
 * @date 19 juin 2014
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\PredicateInterface;

class Not implements PredicateInterface
{
    /**
     * @var string
     */
    protected $specification = 'NOT (%1$s)';

    protected $expression;

    public function __construct($expression = null)
    {
        $this->expression = $expression;
    }

    /**
     * @param null $expression
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return null
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param  string $specification
     * @return self
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
        return $this;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @return array
     */
    public function getExpressionData()
    {
        return array(
            array($this->specification, array($this->expression), array(self::TYPE_VALUE))
        );
    }
}

 