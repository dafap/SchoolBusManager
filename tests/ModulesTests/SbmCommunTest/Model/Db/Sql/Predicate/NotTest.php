<?php
/**
 * Test du Predicate Not
 *
 *
 * @project sbm
 * @package tests
 * @filesource NotTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2021
 * @version 2021-2.5.14
 */
namespace ModulesTests\SbmCommunTest\Model\Db\Sql\Predicate;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use SbmCommun\Model\Db\Sql\Predicate\Not;
use Zend\Db\Sql\Zend\Db\Sql;

class NotTest extends TestCase
{

    public function testNotCreatesNotPredicate()
    {
        $where = new Predicate();
        $where->isNull('foo.bar')
            ->nest()
            ->isNotNull('bar.baz')->and->equalTo('baz.bat', 'foo')->unnest();
        $predicate = new Predicate();
        $predicate->addPredicate(new Not($where));
        $parts = $predicate->getExpressionData();
        $this->assertEquals(1, count($parts));
        $this->assertContains('NOT (%1$s)', $parts[0]);
        $this->assertContains([
            $where
        ], $parts[0]);
        $this->assertContains([
            Not::TYPE_VALUE
        ], $parts[0]);
    }

    public function testEmptyConstructorYieldsNullExpression()
    {
        $not = new Not();
        $this->assertNull($not->getExpression());
    }
}