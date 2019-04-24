<?php
/**
 * Test de fonctionnement d'un SbmCommun\Model\Hydrator\AbstractHydrator
 * 
 * (utilise un TestAsset\HydratorNeutre dérivé de AbstractHydrator)
 * 
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Hydrator 
 * @filesource AbstractHydratorTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Hydrator;

use PHPUnit_Framework_TestCase;
use ModulesTests\SbmCommunTest\Model\TestAsset;

class AbstractHydratorTest extends PHPUnit_Framework_TestCase
{

    // l'objet n'a pas de méthode getArrayCopy()
    public function testWithNullObject()
    {
        $object = null;
        try {
            $hydrator = new TestAsset\HydratorNeutre($object);
            $array = $hydrator->extract($object);
            $this->assertFalse(true, 'Aurait du provoquer une exception !!!');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Zend\Hydrator\Exception\BadMethodCallException', $e);
        }
    }

    public function testWithArray()
    {
        $object = [
            'id' => 123
        ];
        try {
            $hydrator = new TestAsset\HydratorNeutre($object);
            $array = $hydrator->extract($object);
            $this->assertFalse(true, 'Aurait du provoquer une exception !!!');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Zend\Hydrator\Exception\BadMethodCallException', $e);
        }
    }

    public function testWithInvalidClass()
    {
        $object = new \stdClass();
        try {
            $hydrator = new TestAsset\HydratorNeutre($object);
            $array = $hydrator->extract($object);
            $this->assertFalse(true, 'Aurait du provoquer une exception !!!');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Zend\Hydrator\Exception\BadMethodCallException', $e);
        }
    }

    // l'objet présente la bonne méthode
    public function testCorrectWithObjectArraySerializable()
    {
        $data = [
            "foo" => "bar",
            "bar" => "foo",
            "blubb" => "baz",
            "quo" => "blubb"
        ];
        $object = new TestAsset\ObjectArraySerializable($data);
        try {
            $hydrator = new TestAsset\HydratorNeutre($object);
            $array = $hydrator->extract($object);
        } catch (\Exception $e) {
            $this->assertFalse(true, 'N\'aurait du provoquer une exception !!!');
        }
    }

    public function testCorrectWithObjectSbmObjectData()
    {
        $data = [
            "foo" => "bar",
            "bar" => "foo",
            "blubb" => "baz",
            "quo" => "blubb"
        ];
        $object = new TestAsset\ObjectSbmObjectData();
        $object->setDataSource($data);
        try {
            $hydrator = new TestAsset\HydratorNeutre($object);
            $array = $hydrator->extract($object);
        } catch (\Exception $e) {
            $this->assertFalse(true, 'N\'aurait du provoquer une exception !!!');
        }
    }
}