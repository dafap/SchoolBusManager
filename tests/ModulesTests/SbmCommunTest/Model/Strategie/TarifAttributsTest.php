<?php
/**
 * Test de la strategie pour les attributs du tarif
 *
 * Dans la fiche d'un tarif, on trouve les champs codés :
 * - mode
 * - rythme
 * - grille
 * Le codage est précisé dans la classe SbmCommun\Model\Db\Service\Table\Tarifs
 * 
 * Le test porte sur un tableau du même type que ces codages.
 * 
 * @project sbm
 * @package ModulesTests/Model/Strategie
 * @filesource TarifAttributsTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Strategie;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Strategy\TarifAttributs;

class TarifAttributsTest extends PHPUnit_Framework_TestCase
{

    private $strategy;

    private $error_message;

    public function setUp()
    {
        $codes = [
            1 => 'foo',
            2 => 'baz',
            3 => 'tot',
            4 => 'bar'
        ];
        $this->error_message = 'La donnée fourni est inconnu.';
        $this->strategy = new TarifAttributs($codes, $this->error_message);
    }

    public function testExtractListWord()
    {
        $this->assertEquals(3, $this->strategy->extract('tot'));
    }

    public function testExtractUnknowWord()
    {
        try {
            $data = $this->strategy->extract('ziz');
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertEquals($this->error_message . ' : ziz', $e->getMessage());
        }
    }

    public function testExtractNumericValue()
    {
        $this->assertEquals(4, $this->strategy->extract(4));
    }

    public function testExtractNumericValueOutOfRange()
    {
        try {
            $libelle = $this->strategy->extract(13);
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertEquals($this->error_message . ' : 13', $e->getMessage());
        }
    }

    public function testHydrate()
    {
        $this->assertEquals('baz', $this->strategy->hydrate(2));
    }

    public function testHydrateWithValueNotInRange()
    {
        try {
            $libelle = $this->strategy->hydrate(12);
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertEquals($this->error_message . ' : 12', $e->getMessage());
        }
    }

    public function testHydrateWithValueNull()
    {
        try {
            $libelle = $this->strategy->hydrate(null);
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertEquals($this->error_message . ' : ', $e->getMessage());
        }
    }

    public function testHydrateWithValueAsString()
    {
        try {
            $libelle = $this->strategy->hydrate('gag');
            $this->assertTrue(false, 'Aurait du lancer une exception.');
        } catch (\Exception $e) {
            $this->assertEquals($this->error_message . ' : gag', $e->getMessage());
        }
    }
}