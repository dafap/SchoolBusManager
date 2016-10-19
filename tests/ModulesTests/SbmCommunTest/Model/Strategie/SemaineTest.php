<?php
/**
 * Test de strategie pour les jours de la semaine
 *
 * @project sbm
 * @package ModulesTests/Model/Strategie
 * @filesource SemaineTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Strategie;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Strategy\Semaine;

class SemaineTest extends PHPUnit_Framework_TestCase
{

    private $strategy;

    public function setUp()
    {
        $this->strategy = new Semaine();
    }

    public function testExtract()
    {
        // on doit obtenir 14 = 2 + 4 + 8
        $actual = $this->strategy->extract([
            Semaine::CODE_SEMAINE_MARDI,
            Semaine::CODE_SEMAINE_MERCREDI,
            Semaine::CODE_SEMAINE_JEUDI
        ]);
        $expected = 0 * Semaine::CODE_SEMAINE_LUNDI;
        $expected += 1 * Semaine::CODE_SEMAINE_MARDI;
        $expected += 1 * Semaine::CODE_SEMAINE_MERCREDI;
        $expected += 1 * Semaine::CODE_SEMAINE_JEUDI;
        $expected += 0 * Semaine::CODE_SEMAINE_VENDREDI;
        $message = sprintf('attendu : %d  ; obtenu : %d', $expected, $actual);
        $this->assertEquals($expected, $actual, $message);
    }

    public function testHydrate()
    {
        // on doit obtenir array(1, 2, 8)
        $actual = $this->strategy->hydrate(11);
        $b = 11 & (1 << 0);
        if ($b) {
            $expected[] = $b;
        }
        $b = 11 & (1 << 1);
        if ($b) {
            $expected[] = $b;
        }
        $b = 11 & (1 << 2);
        if ($b) {
            $expected[] = $b;
        }
        $b = 11 & (1 << 3);
        if ($b) {
            $expected[] = $b;
        }
        $b = 11 & (1 << 4);
        if ($b) {
            $expected[] = $b;
        }
        $b = 11 & (1 << 5);
        if ($b) {
            $expected[] = $b;
        }
        $message = 'Les tableaux obtenus sont différents';
        $this->assertEquals($expected, $actual, $message);
    }
} 