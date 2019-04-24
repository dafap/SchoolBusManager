<?php
/**
 * Tests d'un ObjectData
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Db/ObjectData
 * @filesource AbstractObjectDataTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Db\ObjectData;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Db\ObjectData\Exception;
use ModulesTests\SbmCommunTest\Model\TestAsset;
use ModulesTests\SbmCommunTest\Model\TestAsset\ObjectIteratorAggregate as MyData;

class AbstractObjectDataTest extends PHPUnit_Framework_TestCase
{

    public function testSetArrayMask()
    {
        $object_data = new TestAsset\ObjectSbmObjectData();
        // doit provoquer une exception
        try {
            $object_data->setArrayMask(null);
            $this->assertTrue(false, 'Le setArrayMask aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // doit provoquer une exception
        try {
            $object_data->setArrayMask('property');
            $this->assertTrue(false, 'Le setArrayMask aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // ne doit pas provoquer une exception
        try {
            $object_data->setArrayMask([]);
            $object_data->setArrayMask([
                'property'
            ]);
        } catch (Exception $e) {
            $this->assertTrue(false,
                'Le setArrayMask n\'aurait pas du lancer une exception.');
        }
    }

    public function testGetterForDataSourceAsArray()
    {
        $init_data = [
            'testId' => 123
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($init_data, 'array');
        $result = false;
        try {
            $result = $object_data->x;
        } catch (Exception $e) {
            $message = sprintf('%s attendu ; %s recu', Exception::class, get_class($e));
            $this->assertInstanceOf(Exception::class, $e, $message);
        }
        $this->assertFalse($result, 'Le get aurait du provoquer une exception.');
        try {
            $result = $object_data->testId;
            $this->assertEquals($init_data['testId'], $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il ne devrait pas y avoir d\'exception.');
        }
    }

    public function testGetterForDataSourceAsArrayIterator()
    {
        $init_data = [
            'testId' => 123
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($init_data, '\ArrayIterator');
        $result = false;
        try {
            $result = $object_data->x;
        } catch (Exception $e) {
            $message = sprintf('%s attendu ; %s recu', Exception::class, get_class($e));
            $this->assertInstanceOf(Exception::class, $e, $message);
        }
        $this->assertFalse($result, 'Le get aurait du provoquer une exception.');
        try {
            $result = $object_data->testId;
            $this->assertEquals($init_data['testId'], $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il ne devrait pas y avoir d\'exception.');
        }
    }

    public function testGetterForDataSourceAsIteratorAggregate()
    {
        $init_data = [
            'property1' => "Propriété publique numéro un",
            'property2' => "Propriété publique numéro deux",
            'property3' => "Propriété publique numéro trois",
            'property4' => 'dernière propriété'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource(new MyData($init_data), '\IteratorAggregate');
        $result = false;
        try {
            $result = $object_data->x;
        } catch (Exception $e) {
            $message = sprintf('%s attendu ; %s recu', Exception::class, get_class($e));
            $this->assertInstanceOf(Exception::class, $e, $message);
        }
        $this->assertFalse($result, 'Le get aurait du provoquer une exception.');
        try {
            $result = $object_data->property3;
            $this->assertEquals($init_data['property3'], $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il ne devrait pas y avoir d\'exception.');
        }
    }

    public function testSetterForDataSourceAsArray()
    {
        $init_data = [
            'testId' => 123
        ];
        $array_mask = [
            'foo',
            'bar'
        ];
        $new_value = 987;
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource([
            'foo' => null
        ], 'array');
        try {
            $object_data->testId = $new_value;
            $this->assertTrue(false, 'Le set aurait du provoquer une exception (1).');
        } catch (Exception $e) {
        }
        $object_data->setDataSource($init_data, 'array');
        $object_data->setArrayMask($array_mask);
        try {
            $object_data->testId = $new_value;
        } catch (Exception $e) {
            $this->assertTrue(false,
                'Il ne devrait pas y avoir d\'exception car le paramètre est déjà affecté.');
        }
        $array_mask = [
            'testId',
            'foo',
            'bar'
        ];
        $object_data->setArrayMask($array_mask);
        try {
            $object_data->testId = $new_value;
            $result = $object_data->testId;
            $this->assertEquals($new_value, $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il ne devrait pas y avoir d\'exception.');
        }
        try {
            $object_data->bar = $new_value;
            $result = $object_data->bar;
            $this->assertEquals($new_value, $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false,
                'Il ne devrait pas y avoir d\'exception car le paramètre est autorise dans array_mask.');
        }
        $result = false;
        try {
            $object_data->x = $new_value;
            $result = true;
        } catch (\Exception $e) {
            $message = sprintf('%s attendu ; %s recu', Exception::class, get_class($e));
            $this->assertInstanceOf(Exception::class, $e, $message);
        }
        $this->assertFalse($result, 'Le set aurait du provoquer une exception (2).');
    }

    public function testSetterForDataSourceAsArrayIterator()
    {
        $init_data = [
            'testId' => 123
        ];
        $array_mask = [
            'foo',
            'bar'
        ];
        $new_value = 987;
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($init_data, '\ArrayIterator');
        $object_data->setArrayMask($array_mask);
        try {
            $object_data->testId = $new_value;
            $result = $object_data->testId;
            $this->assertEquals($new_value, $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false,
                'Il ne devrait pas y avoir d\'exception car le parametre est deja affecte.');
        }
        try {
            $object_data->foo = $new_value;
            $result = $object_data->foo;
            $this->assertEquals($new_value, $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false,
                'Il ne devrait pas y avoir d\'exception car le parametre est autorise dans array_mask.');
        }
        $result = false;
        try {
            $object_data->x = $new_value;
            $result = true;
        } catch (\Exception $e) {
            $message = sprintf('%s attendu ; %s recu', Exception::class, get_class($e));
            $this->assertInstanceOf(Exception::class, $e, $message);
        }
        $this->assertFalse($result, 'Le set aurait du provoquer une exception.');
    }

    public function testSetterForDataSourceAsIteratorAggregate()
    {
        $init_data = [
            'property1' => 'quelque chose',
            'property2' => 123
        ];
        $new_value = 987;
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource(new MyData($init_data), '\IteratorAggregate');
        $array_mask = [
            'foo',
            'bar'
        ];
        try {
            // ici il ne doit pas y avoir d'exception car property2 est définie
            $object_data->property2 = $new_value;
            $result = $object_data->property2;
            $this->assertEquals($new_value, $result, 'Résultat incorrect.');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il ne devrait pas y avoir d\'exception.');
        }
        $result = false;
        try {
            // ici il doit y avoir une exception car array_mask n'est pas utilisé
            // pour un IteratorAggregate et foo n'est pas deja affecte.
            $object_data->foo = $new_value;
            $result = true;
        } catch (\Exception $e) {
            $message = sprintf('%s attendu ; %s recu', Exception::class, get_class($e));
            $this->assertInstanceOf(Exception::class, $e, $message);
        }
        try {
            $object_data->x = $new_value;
            $result = true;
        } catch (\Exception $e) {
            $message = sprintf('%s attendu ; %s recu', Exception::class, get_class($e));
            $this->assertInstanceOf(Exception::class, $e, $message);
        }
        $this->assertFalse($result, 'Le set aurait du provoquer une exception.');
    }

    public function testSetAreNullable()
    {
        $object_data = new TestAsset\ObjectSbmObjectData();
        // doit provoquer une exception
        try {
            $object_data->setAreNullable(null);
            $this->assertTrue(false, 'Le setAreNullable aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // doit provoquer une exception
        try {
            $object_data->setAreNullable('property');
            $this->assertTrue(false, 'Le setAreNullable aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // ne doit pas provoquer une exception
        try {
            $object_data->setAreNullable([]);
            $object_data->setAreNullable([
                'property'
            ]);
        } catch (Exception $e) {
            $this->assertTrue(false,
                'Le setAreNullable n\'aurait pas du lancer une exception.');
        }
    }

    public function testGetArrayMask()
    {
        $mask = [
            'property1',
            'property2'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        try {
            $object_data->setArrayMask($mask);
            $this->assertEquals($mask, $object_data->getArrayMask(),
                'getArrayMask ne rend pas le bon masque.');
        } catch (Exception $e) {
            $this->assertTrue(false,
                'getArrayMask ou setArrayMask n\'auraient pas du lancer une exception.');
        }
    }

    public function testSetCalculateFields()
    {
        $calculate_fields = [
            'property1',
            'property2'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        // doit provoquer une exception
        try {
            $object_data->setCalculateFields(null);
            $this->assertTrue(false,
                'Le setCalculateFields aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // doit provoquer une exception
        try {
            $object_data->setCalculateFields('property');
            $this->assertTrue(false,
                'Le setCalculateFields aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // ne doit pas provoquer une exception
        try {
            $object_data->setCalculateFields([]);
            $object_data->setCalculateFields($calculate_fields);
        } catch (Exception $e) {
            $this->assertTrue(false,
                'Le setCalculateFields n\'aurait pas du lancer une exception.');
        }
    }

    public function testAddCalculateField()
    {
        $calculate_fields = [
            'property1',
            'property2'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        // doit provoquer une exception
        try {
            $object_data->addCalculateField(null);
            $this->assertTrue(false,
                'Le addCalculateField aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // doit provoquer une exception
        try {
            $object_data->addCalculateField([]);
            $this->assertTrue(false,
                'Le addCalculateField aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // doit provoquer une exception
        try {
            $object_data->addCalculateField([
                'property'
            ]);
            $this->assertTrue(false,
                'Le addCalculateField aurait du lancer une exception.');
        } catch (Exception $e) {
        }
        // ne doit pas provoquer une exception
        foreach ($calculate_fields as $field) {
            try {
                $object_data->addCalculateField($field);
            } catch (Exception $e) {
                $this->assertTrue(false,
                    'Le addCalculateField n\'aurait pas du lancer une exception.');
            }
        }
        $this->assertEquals($calculate_fields, $object_data->getCalculateFields(),
            'La liste des champs calcules est incorrecte.');
    }

    public function testSetCalculateFieldsThenAddCalculateField()
    {
        $calculate_fields = [
            'property1',
            'property2'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        try {
            $object_data->setCalculateFields($calculate_fields);
            $object_data->addCalculateField('property3');
            $expected = $calculate_fields;
            $expected[] = 'property3';
            $this->assertEquals($expected, $object_data->getCalculateFields(),
                'La liste des champs calcules est incorrecte.');
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il n\'aurait pas du avoir d\'exception.');
        }
    }

    public function testExchangeArrayWithDataSourceAsArray()
    {
        $data = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        try {
            $object_data->exchangeArray($data);
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il n\'aurait pas du avoir d\'exception.');
        }
    }

    public function testExchangeArrayWithDataSourceAsArrayIterator()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $data = new \ArrayIterator($array);
        $object_data = new TestAsset\ObjectSbmObjectData();
        try {
            $object_data->exchangeArray($data);
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il n\'aurait pas du avoir d\'exception.');
        }
    }

    public function testExchangeArrayWithDataSourceAsIteratorAggregate()
    {
        $init_data = [
            'property1' => '',
            'property2' => 'quelque chose'
        ];
        $data = new MyData($init_data);
        $data->property1 = 123;
        $data->property3 = null;
        $object_data = new TestAsset\ObjectSbmObjectData();
        try {
            $object_data->exchangeArray($data);
        } catch (Exception $e) {
            $this->assertTrue(false, 'Il n\'aurait pas du avoir d\'exception.');
        }
    }

    public function testGetArrayCopyWithDataSourceAsArray()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($array, 'array');
        $this->assertEquals($array, $object_data->getArrayCopy(),
            'GetArrayCopy ne renvoie pas le bon tableau.');
    }

    public function testGetArrayCopyWithDataSourceAsArrayIterator()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $data = new \ArrayIterator($array);
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($data, '\ArrayIterator');
        $this->assertEquals($array, $object_data->getArrayCopy(),
            'GetArrayCopy ne renvoie pas le bon tableau.');
    }

    public function testGetArrayCopyWithDataSourceAsIteratorAggregate()
    {
        $array = [
            'property1' => "Propriété publique numéro un",
            'property2' => "Propriété publique numéro deux",
            'property3' => "Propriété publique numéro trois",
            'property4' => 'dernière propriété'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource(new MyData($array), '\IteratorAggregate');
        $this->assertEquals($array, $object_data->getArrayCopy(),
            'GetArrayCopy ne renvoie pas le bon tableau.');
        $object_data->setDataSource(new MyData(), '\iteratorAggregate');
        $this->assertEquals([], $object_data->getArrayCopy(),
            'GetArrayCopy ne renvoie pas un tableau vide.');
    }

    public function testGetIteratorWithDataSourceAsArray()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($array, 'array');
        $this->assertInstanceOf('\Iterator', $object_data->getIterator(),
            'Ne renvoie pas un Iterator.');
    }

    public function testGetIteratorWithDataSourceAsArrayIterator()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $data = new \ArrayIterator($array);
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($data, '\ArrayIterator');
        $this->assertInstanceOf('\Iterator', $object_data->getIterator(),
            'Ne renvoie pas un Iterator.');
    }

    public function testGetIteratorWithDataSourceAsIteratorAggregate()
    {
        $array = [
            'property1' => "Propriété publique numéro un",
            'property2' => "Propriété publique numéro deux",
            'property3' => "Propriété publique numéro trois",
            'property4' => 'dernière propriété'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource(new MyData($array), '\IteratorAggregate');
        $this->assertInstanceOf('\Iterator', $object_data->getIterator(),
            'Ne renvoie pas un Iterator.');
        $object_data->setDataSource(new MyData(), '\IteratorAggregate');
        $this->assertInstanceOf('\Iterator', $object_data->getIterator(),
            'Ne renvoie pas un Iterator.');
    }

    public function testIsSetWithDataSourceAsArray()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($array, 'array');
        $this->assertTrue(isset($object_data->property2),
            'Pourtant la propriété `property2` existe !');
        $this->assertFalse(isset($object_data->property9),
            'Pourtant la propriété `property9` n\'existe pas !');
    }

    public function testIsSetWithDataSourceAsArrayIterator()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $data = new \ArrayIterator($array);
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($data, '\ArrayIterator');
        $this->assertTrue(isset($object_data->property2),
            'Pourtant la propriété `property2` existe !');
        $this->assertFalse(isset($object_data->property9),
            'Pourtant la propriété `property9` n\'existe pas !');
    }

    public function testIsSetWithDataSourceAsIyeratorAggregate()
    {
        $array = [
            'property1' => "Propriété publique numéro un",
            'property2' => "Propriété publique numéro deux",
            'property3' => "Propriété publique numéro trois",
            'property4' => 'dernière propriété'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource(new MyData($array), '\IteratorAggregate');
        $this->assertTrue(isset($object_data->property2),
            'Pourtant la propriété `property2` existe !');
        $this->assertFalse(isset($object_data->property9),
            'Pourtant la propriété `property9` n\'existe pas !');
    }

    public function testUnSetWithDataSourceAsArray()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($array, 'array');
        unset($object_data->property2);
        $this->assertFalse(isset($object_data->property2),
            'Pourtant la propriété `property2` ne devrait plus exister !');
        unset($object_data->property9);
    }

    public function testUnSetWithDataSourceAsArrayIterator()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $data = new \ArrayIterator($array);
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($data, '\ArrayIterator');
        unset($object_data->property2);
        $this->assertFalse(isset($object_data->property2),
            'Pourtant la propriété `property2` ne devrait plus exister !');
        unset($object_data->property9);
    }

    public function testUnSetWithDataSourceAsIyeratorAggregate()
    {
        $array = [
            'property1' => "Propriété publique numéro un",
            'property2' => "Propriété publique numéro deux",
            'property3' => "Propriété publique numéro trois",
            'property4' => 'dernière propriété'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource(new MyData($array), '\IteratorAggregate');
        unset($object_data->property2);
        $this->assertFalse(isset($object_data->property2),
            'Pourtant la propriété `property2` ne devrait plus exister !');
        unset($object_data->property9);
    }

    public function testCountWithDataSourceAsArray()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($array, 'array');
        $this->assertEquals(3, $object_data->count(),
            'La méthode `count()` renvoie une valeur incorrecte.');
    }

    public function testCountWithDataSourceAsArrayIterator()
    {
        $array = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $data = new \ArrayIterator($array);
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource($data, '\ArrayIterator');
        $this->assertEquals(3, $object_data->count(),
            'La méthode `count()` renvoie une valeur incorrecte.');
    }

    public function testCountWithDataSourceAsIyeratorAggregate()
    {
        $array = [
            'property1' => "Propriété publique numéro un",
            'property2' => "Propriété publique numéro deux",
            'property3' => "Propriété publique numéro trois",
            'property4' => 'dernière propriété'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setDataSource(new MyData($array), '\IteratorAggregate');
        $this->assertEquals(4, $object_data->count(),
            'La méthode `count()` renvoie une valeur incorrecte.');
    }

    public function testIsUnchanged()
    {
        $array1 = [
            'property1' => 123,
            'property2' => "Propriété publique numéro deux",
            'property3' => null
        ];
        $array2 = [
            'property2' => "Propriété publique numéro deux",
            'property3' => null,
            'property4' => "Propriété publique numéro quatre"
        ];
        $object_data1 = new TestAsset\ObjectSbmObjectData();
        $object_data2 = clone $object_data1;
        $object_data1->setDataSource($array1);
        $object_data2->setDataSource($array2);
        $this->assertTrue($object_data2->isUnchanged($object_data1),
            'Aurait du dire que les objets sont inchanges car les proprietes communes ont les memes valeurs.');
    }

    public function testGetValidId()
    {
        $object_data = new TestAsset\ObjectSbmObjectData();
        // scalaire
        $id_field_name = 'testId';
        $object_data->setIdFieldName($id_field_name);
        $id = 123;
        $this->assertEquals($id, $object_data->getValidId($id), 'id est un entier');
        $id = '0123344D';
        $this->assertEquals($id, $object_data->getValidId($id), 'id est une chaine');
        // tableau
        $id_field_name = [
            'test1Id',
            'test2Id'
        ];
        $object_data->setIdFieldName($id_field_name);
        $id1 = [
            'test1Id' => 123,
            'test2Id' => '0123344D'
        ];
        $this->assertEquals($id1, $object_data->getValidId($id1),
            'id est un tableau associatif');
        $id2 = '123|0123344D';
        $this->assertEquals($id1, $object_data->getValidId($id2),
            'id est une cle composee sous forme d\'une chaine de caracteres dont les parties sont separees par |.');
    }

    public function testGetId()
    {
        $array = [
            'testId' => 123,
            'property2' => 'foo',
            'property3' => 'baz'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->exchangeArray($array);

        // scalaire
        $id_field_name = 'testId';
        $object_data->setIdFieldName($id_field_name);
        $this->assertEquals(123, $object_data->getId(),
            'Ne renvoie pas le bon Id (entier)');
        $id_field_name = 'property2';
        $object_data->setIdFieldName($id_field_name);
        $this->assertEquals('foo', $object_data->getId(),
            'Ne renvoie pas le bon Id (chaine)');

        // tableau
        $id_field_name = [
            'testId',
            'property2'
        ];
        $expected = $array;
        unset($expected['property3']);
        $object_data->setIdFieldName($id_field_name);
        $this->assertEquals($expected, $object_data->getId(),
            'Ne renvoie pas le bon Id (tableau)');
    }

    public function testExchangeArrayWithMask()
    {
        $data = [
            'property1' => 123,
            'property2' => "Valeur inutile qui ne sera pas dans l'objet_data",
            'property3' => null
        ];
        $mask = [
            'testId',
            'property1',
            'property3'
        ];
        $object_data = new TestAsset\ObjectSbmObjectData();
        $object_data->setArrayMask($mask);

        // test avec un tableau
        try {
            $object_data->exchangeArray($data);
            unset($data['property2']);
            $this->assertEquals($data, $object_data->getArrayCopy(),
                'L\'affectation est incorrecte.');
        } catch (Exception $e) {
            $this->assertTrue(false, 'array: Il n\'aurait pas du y avoir d\'exception.');
        }

        // test avec un ArrayIterator
        try {
            $object_data->exchangeArray(new \ArrayIterator($data));
            unset($data['property2']);
            $this->assertEquals($data, $object_data->getArrayCopy(),
                'L\'affectation est incorrecte.');
        } catch (Exception $e) {
            $this->assertTrue(false,
                'ArrayIterator: Il n\'aurait pas du y avoir d\'exception.');
        }

        // test avec un IteratorAggregate
        try {
            $data = [
                'property1' => 'Propriété publique numéro un',
                'property2' => 'Propriété publique numéro deux',
                'property3' => 'Propriété publique numéro trois'
            ];
            $object_data->exchangeArray(new myData($data));
            unset($data['property2']);
            $this->assertEquals($data, $object_data->getArrayCopy(),
                'L\'affectation est incorrecte.');
        } catch (Exception $e) {
            $this->assertTrue(false,
                'IteratorInterface: Il n\'aurait pas du y avoir d\'exception.');
        }
    }
}


 