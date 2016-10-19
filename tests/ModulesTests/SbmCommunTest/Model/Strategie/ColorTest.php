<?php
/**
 * Test de strategie pour les couleurs
 *
 * - extract supprime le # devant la valeur hexa
 * - hydrate 
 *    rajoute le # devant une valeur hexadécimale donnée (exemple ff6347
 *    renvoie la valeur hexa de la couleur si elle est référencée (exemple tomato)
 *    renvoie #000000 si la couleur n'est pas référencée (exemple azerty)
 *    renvoie #000000 si la couleur est vide (null ou '' ou 0 ou false)
 * 
 * @project sbm
 * @package ModulesTests/Model/Strategie
 * @filesource ColorTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Strategie;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Strategy\Color;

class ColorTest extends PHPUnit_Framework_TestCase
{

    private $strategy;

    public function setUp()
    {
        $this->strategy = new Color();
    }

    public function testExtract()
    {
        $valeur = '#ff6347';
        $expected = 'ff6347';
        $actual = $this->strategy->extract($valeur);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }

    public function testHydrateNull()
    {
        $value = null;
        $expected = '#000000';
        $actual = $this->strategy->hydrate($value);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }

    public function testHydrateVide()
    {
        $value = '';
        $expected = '#000000';
        $actual = $this->strategy->hydrate($value);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }

    public function testHydrateZero()
    {
        $value = 0;
        $expected = '#000000';
        $actual = $this->strategy->hydrate($value);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }

    public function testHydrateFalse()
    {
        $value = false;
        $expected = '#000000';
        $actual = $this->strategy->hydrate($value);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }

    public function testHydrateWordReferenced()
    {
        $value = 'tomato';
        $expected = '#ff6347';
        $actual = $this->strategy->hydrate($value);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }

    public function testHydrateWordUnreferenced()
    {
        $value = 'azerty';
        $expected = '#000000';
        $actual = $this->strategy->hydrate($value);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }

    public function testHydrateHexa()
    {
        $value = 'abcde';
        $expected = '#abcde';
        $actual = $this->strategy->hydrate($value);
        $this->assertEquals($expected, $actual, 'Valeur incorrecte après extract');
    }
}