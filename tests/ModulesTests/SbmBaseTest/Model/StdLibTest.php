<?php
/**
 * Test de la classe SbmCommun\Model\StdLib
 *
 * @project sbm
 * @package ModulesTests/SbmBaseTest/Model
 * @filesource StdLibTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 août 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests\SbmBaseTest\Model;

use PHPUnit\Framework\TestCase;
use SbmBase\Model\StdLib;
use SbmBase\Model\Exception;

class StdLibTest extends TestCase
{

    public function testEntityName()
    {
        $prefix = 'azerty';
        $entityName = 'baz';
        $entityType = 'table';
        $expected = 'azerty_t_baz';
        $actual = StdLib::entityName($entityName, $entityType, $prefix);
        $this->assertEquals($expected, $actual, 'Le calcul du nom est faux pour une table avec préfixe.');
        $entityType = 'system';
        $expected = 'azerty_s_baz';
        $actual = StdLib::entityName($entityName, $entityType, $prefix);
        $this->assertEquals($expected, $actual, 'Le calcul du nom est faux pour une table système avec préfixe.');
        $entityType = 'vue';
        $expected = 'azerty_v_baz';
        $actual = StdLib::entityName($entityName, $entityType, $prefix);
        $this->assertEquals($expected, $actual, 'Le calcul du nom est faux pour une vue avec préfixe.');
        // sans préfixe
        $prefix = '';
        $entityName = 'baz';
        $entityType = 'table';
        $expected = 't_baz';
        $actual = StdLib::entityName($entityName, $entityType, $prefix);
        $this->assertEquals($expected, $actual, 'Le calcul du nom est faux pour une table sans préfixe.');
        $entityType = 'system';
        $expected = 's_baz';
        $actual = StdLib::entityName($entityName, $entityType, $prefix);
        $this->assertEquals($expected, $actual, 'Le calcul du nom est faux pour une table système sans préfixe.');
        $entityType = 'vue';
        $expected = 'v_baz';
        $actual = StdLib::entityName($entityName, $entityType, $prefix);
        $this->assertEquals($expected, $actual, 'Le calcul du nom est faux pour une vue sans préfixe.');
    }

    public function testArrayKeysExists()
    {
        // arguments corrects, réponse true
        $search = [
            'foo' => [
                'bar' => 'value1',
                'baz' => null
            ]
        ];
        $this->assertTrue(StdLib::array_keys_exists([
            'foo',
            'baz'
        ], $search));
        // arguments corrects, réponse false
        $this->assertFalse(StdLib::array_keys_exists([
            'foo',
            'goz'
        ], $search));
        // argument key incorrect
        try {
            $result = StdLib::array_keys_exists('foo', $search);
            $this->assertFalse(true, 'Aurait du lancer une excption.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'Exception d\'un mauvais type.');
        }
        // argument search incorrect
        $this->assertFalse(StdLib::array_keys_exists([
            'foo',
            'goz'
        ], null));
    }

    /**
     * Test avec tableaux associatifs imbriqués et avec tableau simple et clés numériques.
     */
    public function testArrayToObject()
    {
        // tableau simple ou avec des clés numériques
        $obj = StdLib::arrayToObject([
            'foo',
            'baz'
        ]);
        $this->assertInstanceOf(\StdClass::class, $obj);
        $this->assertEquals('foo', $obj->{'0'});
        // tableau associatif (le dernier tableau a une clé numérique)
        $array = [
            'foo' => [
                'bar' => 'value1',
                'baz' => [
                    3 => 'gud',
                    'pop' => null
                ]
            ]
        ];
        $obj = StdLib::arrayToObject($array);
        $this->assertObjectHasAttribute('foo', $obj);
        $this->assertObjectHasAttribute('baz', $obj->foo);
        $this->assertEquals('value1', $obj->foo->bar);
        // le paramètre n'est pas un tableau
        $param = new \StdClass();
        $param->foo = [
            'a',
            'b' => 'c'
        ];
        $this->assertSame($param, StdLib::arrayToObject($param));
    }

    public function testGetParam()
    {
        // tout marche bien, 'foo' est une clé du tableau
        $array = [
            'foo' => 'value_foo',
            'bar' => 'value_bar'
        ];
        try {
            $result = StdLib::getParam('foo', $array, 'baz');
            $this->assertEquals('value_foo', $result);
        } catch (\Exception $e) {
            $this->assertFalse('true', 'Ne devrait pas provoquer une exception.');
        }
        // 'foo' n'est pas une clé du tableau, on obtient la valeur 'baz' par défaut
        $array = [
            'fot' => 'value_foo',
            'bar' => 'value_bar'
        ];
        try {
            $result = StdLib::getParam('foo', $array, 'baz');
            $this->assertEquals('baz', $result);
        } catch (\Exception $e) {
            $this->assertFalse('true', 'Ne devrait pas provoquer une exception.');
        }
        // 'foo' n'est pas une clé du tableau, on obtient la valeur null par défaut
        $array = [
            'fot' => 'value_foo',
            'bar' => 'value_bar'
        ];
        try {
            $result = StdLib::getParam('foo', $array);
            $this->assertNull($result, 'La valeur par défaut est null.');
        } catch (\Exception $e) {
            $this->assertFalse('true', 'Ne devrait pas provoquer une exception.');
        }
        // $array n'est pas un tableau
        $array = null;
        try {
            $result = StdLib::getParam('foo', $array, 'baz');
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        // Param n'est pas une chaine ou un nombre
        $array = [
            'foo' => 'value_foo',
            'bar' => 'value_bar'
        ];
        try {
            $result = StdLib::getParam(null, $array, 'baz');
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
    }

    /**
     * Si le premier paramètre ($index) n'est pas un tableau, on appelle getParam déjà testé.
     * Il reste les cas suivants :
     * - le premier paramètre est un tableau de clés
     * - l'un des éléments du tableau d'index n'est pas une clé du tableau
     * - le second paramètre ($array) n'est pas un tableau
     */
    public function testGetParamR()
    {
        // cas n°1 : renvoie la bonne valeur
        $array = [
            'foo' => [
                'un' => 'v1',
                'baz' => [
                    'bar' => 'value'
                ]
            ]
        ];
        try {
            $result = StdLib::getParamR([
                'foo',
                'baz',
                'bar'
            ], $array);
            $this->assertEquals('value', $result);
        } catch (\Exception $e) {
            $this->assertFalse('true', 'Ne devrait pas provoquer une exception.');
        }
        // cas n°2 : renvoie la valeur par défaut
        $array = [
            'foo' => [
                'un' => 'v1',
                'baz' => [
                    'bar' => 'value'
                ]
            ]
        ];
        try {
            $result = StdLib::getParamR([
                'foo',
                'juz',
                'bar'
            ], $array, 'default');
            $this->assertEquals('default', $result);
        } catch (\Exception $e) {
            $this->assertFalse('true', 'Ne devrait pas provoquer une exception.');
        }
        // cas n°3 : lance une exception
        $array = null;
        try {
            $result = StdLib::getParam([
                'foo',
                'guz'
            ], $array, 'baz');
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        // cas n°4 : appelle getParam
        $sub = [
            'un' => 'v1',
            'baz' => [
                'bar' => 'value'
            ]
        ];
        $array = [
            'foo' => $sub
        ];
        try {
            $result = StdLib::getParamR('foo', $array);
            $this->assertSame($sub, $result);
        } catch (\Exception $e) {
            $this->assertFalse('true', 'Ne devrait pas provoquer une exception à ce niveau.');
        }
    }

    public function testConcatPath()
    {
        // cas n°1 et 2 : l'un des paramètre n'est pas une chaine de caractères
        try {
            $result = StdLib::concatPath(null, 'aerty');
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        try {
            $result = StdLib::concatPath('aerty', 0);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        // cas n° 3 : $file commence par //
        try {
            $file = '//qsdfgh';
            $result = StdLib::concatPath('aerty', $file);
            $this->assertEquals($file, $result);
        } catch (\Exception $e) {
            $this->assertFalse('true', 'Ne devrait pas provoquer une exception à ce niveau.');
        }
        // cas n° 4 : concaténation en remplaçant les \ par des / et en évitant les doubles //
        try {
            $path = '\\azerty' . DIRECTORY_SEPARATOR;
            $file = '/qs//dfg.sdf';
            $expected = '/azerty/qs/dfg.sdf';
            $result = StdLib::concatPath($path, $file);
            $this->assertEquals($expected, $result, $result);
        } catch (\Exception $e) {
            $this->assertFalse('true', $e->getMessage());
        }
    }

    public function testAddQuotesToString()
    {
        // valeur numérique
        $expected = '123';
        $this->assertSame($expected, StdLib::addQuotesToString(123));
        // valeurs true / false
        $this->assertEquals(1, StdLib::addQuotesToString('true'));
        $this->assertEquals(0, StdLib::addQuotesToString('false'));
        // valeurs vrai / faux
        $this->assertEquals(1, StdLib::addQuotesToString('vrai'));
        $this->assertEquals(0, StdLib::addQuotesToString('faux'));
        // valeurs yes / no
        $this->assertEquals(1, StdLib::addQuotesToString('yes'));
        $this->assertEquals(0, StdLib::addQuotesToString('no'));
        // valeurs oui / non
        $this->assertEquals(1, StdLib::addQuotesToString('oui'));
        $this->assertEquals(0, StdLib::addQuotesToString('non'));
        // valeur chaine
        $chaine = " J'ai un peu dormi.   ";
        $expected = "'J\'ai un peu dormi.'";
        $this->assertEquals($expected, StdLib::addQuotesToString($chaine));
        // ce n'est pas une chaine ni une valeur numérique
        foreach ([
            null,
            false,
            true,
            new \StdClass(),
            [
                1,
                2,
                3,
                'key' => 'value'
            ]
        ] as $actual) {
            $this->assertSame($actual, StdLib::addQuotesToString($actual));
        }
    }

    public function testGetArrayFromString()
    {
        // une chaine bien formée : clé1 => valeur1, clé2 => valeur2
        $str = 'clé1 => valeur1, clé2 => valeur2';
        $expected = [
            'clé1' => 'valeur1',
            'clé2' => 'valeur2'
        ];
        $this->assertSame($expected, StdLib::getArrayFromString($str));
        // une chaine dont les clés sont numérique
        $str = '1 => valeur1, 2 => valeur2';
        $expected = [
            1 => 'valeur1',
            2 => 'valeur2'
        ];
        $this->assertSame($expected, StdLib::getArrayFromString($str));
        // une chaine dont les clés sont booléennes
        $str = 'false => valeur1, true => valeur2';
        $expected = [
            false => 'valeur1',
            true => 'valeur2'
        ];
        $this->assertSame($expected, StdLib::getArrayFromString($str));
        
        // une chaine contenant au moins une virgule
        $str = "Une chaine assez simple, mais j'ai une virgule";
        $expected = [
            "Une chaine assez simple",
            "mais j'ai une virgule"
        ];
        $this->assertSame($expected, StdLib::getArrayFromString($str));
        // une chaine sans virgule
        $str = "Ce n'est qu'un au revoir.";
        $expected = [
            $str
        ];
        $this->assertSame($expected, StdLib::getArrayFromString($str));
        // null
        $str = null;
        $expected = [
            0 => ''
        ];
        $this->assertSame($expected, StdLib::getArrayFromString($str));
        // objet
        $str = new \StdClass();
        try {
            $result = StdLib::getArrayFromString($str);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        // tableau
        $str = [
            'foo' => 'baz',
            'guz'
        ];
        try {
            $result = StdLib::getArrayFromString($str);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
    }

    public function testTranslateData()
    {
        // une traduction
        $data = 'Me';
        $array = [
            'Lu' => 'Lundi',
            'Ma' => 'Mardi',
            'Me' => 'Mercredi'
        ];
        $this->assertEquals('Mercredi', StdLib::translateData($data, $array));
        // un élément non traduit
        $data = 'Sa';
        $this->assertSame($data, StdLib::translateData($data, $array));
        // null
        $data = null;
        $this->assertSame('', StdLib::translateData($data, $array));
        // tableau indexé
        $data = [
            'Lu',
            'Ma',
            'Sa'
        ];
        $expected = 'Lundi+Mardi+Sa';
        $this->assertSame($expected, StdLib::translateData($data, $array));
        // tableau associatif
        $data = [
            1 => 'Ma',
            2 => 'Me'
        ];
        try {
            $result = StdLib::translateData($data, $array);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        // objet
        $data = new \StdClass();
        try {
            $result = StdLib::translateData($data, $array);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
    }
    
    public function testFormatData()
    {
        // data est un entier, précision n'a aucun effet
        $data = 1234;
        $result = StdLib::formatData($data, 1, 8);
        $this->assertEquals($result, StdLib::formatData($data, 3, 8));
        $this->assertEquals('    1234', $result);
        $result = StdLib::formatData($data, -1, '08');
        $this->assertEquals('00001234', $result);
        // data est une chaine de digits, précision a le même effet que sur un float
        $data = "1234";
        $result = StdLib::formatData(1234.0, 3, 9);
        $this->assertEquals($result, StdLib::formatData($data, 3, 9));
        $this->assertEquals(' 1234.000', $result);
        $result = StdLib::formatData(1234.0, 3, '09');
        $this->assertEquals('01234.000', $result);
        // data est une chaine, précision indique une troncature saur si elle est négative
        $data = "un bel exemple.";
        $result = StdLib::formatData($data, -1, 20);
        $this->assertEquals('     un bel exemple.', $result);
        $result = StdLib::formatData($data, 5, 9);
        $this->assertEquals('    un be', $result);
        // data est null ou vide
        $data = null;
        $result = StdLib::formatData($data, -1, 20);
        $this->assertEquals($result, StdLib::formatData('', -1, 20));
        $this->assertEquals('                    ', $result);
        // data est un tableau ou un objet
        $data = [];
        try {
            $result = StdLib::formatData($data, -1, 20);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        $data = new \StdClass();
        try {
            $result = StdLib::formatData($data, -1, 20);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }        
    }
    
    public function testIsIndexedArray()
    {
        // les erreurs
        $data = null;
        try {
            $result = StdLib::isIndexedArray($data);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        $data = 12;
        try {
            $result = StdLib::isIndexedArray($data);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        $data = 'azerty';
        try {
            $result = StdLib::isIndexedArray($data);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        $data = new \StdClass();
        try {
            $result = StdLib::isIndexedArray($data);
            $this->assertFalse('true', 'Aurait du provoquer une exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'On a reçu une exception d\'un mauvais type.');
        }
        // un tableau indexé
        $data = [12, null, 'azerty'];
        $this->assertTrue(StdLib::isIndexedArray($data));
        // un tableau associatif
        $data = [12, 4 => 'azerty'];
        $this->assertFalse(StdLib::isIndexedArray($data));
        // des tableaux indexés emboités
        $data = [['az', 'er'],['qs', 3]];
        $this->assertTrue(StdLib::isIndexedArray($data));
        // un tableau associatif dans un tableau indexé
        $data = [['az', 'er'],['qs' => 3, 123]];
        $this->assertTrue(StdLib::isIndexedArray($data));
        
    }
}
 