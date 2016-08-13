<?php
/**
 * Test de strategie pour les niveaux d'enseignement
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package ModulesTests/Model/Strategie
 * @filesource NiveauTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Strategie;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Strategy\Niveau;

class NiveauTest extends PHPUnit_Framework_TestCase
{

    private $strategy;

    public function setUp()
    {
        $this->strategy = new Niveau();
    }

    public function testExtract()
    {
        // on doit obtenir 11 = 1 + 2 + 8
        $actual = $this->strategy->extract([
            Niveau::CODE_NIVEAU_MATERNELLE,
            Niveau::CODE_NIVEAU_ELEMENTAIRE,
            Niveau::CODE_NIVEAU_SECOND_CYCLE
        ]);
        $expected = 1 * Niveau::CODE_NIVEAU_MATERNELLE;
        $expected += 1 * Niveau::CODE_NIVEAU_ELEMENTAIRE;
        $expected += 0 * Niveau::CODE_NIVEAU_PREMIER_CYCLE;
        $expected += 1 * Niveau::CODE_NIVEAU_SECOND_CYCLE;
        $expected += 0 * Niveau::CODE_NIVEAU_POST_BAC;
        $message = sprintf('attendu : %d  ; obtenu : %d', $expected, $actual);
        $this->assertEquals($expected, $actual, $message);
    }

    public function testHydrate()
    {
        // on doit obtenir array(2, 4, 8)
        $actual = $this->strategy->hydrate(14);
        $b = 14 & (1 << 0);
        if ($b) {
            $expected[] = $b;
        }
        $b = 14 & (1 << 1);
        if ($b) {
            $expected[] = $b;
        }
        $b = 14 & (1 << 2);
        if ($b) {
            $expected[] = $b;
        }
        $b = 14 & (1 << 3);
        if ($b) {
            $expected[] = $b;
        }
        $b = 14 & (1 << 4);
        if ($b) {
            $expected[] = $b;
        }
        $b = 14 & (1 << 5);
        if ($b) {
            $expected[] = $b;
        }
        $message = 'Les tableaux obtenus sont différents';
        $this->assertEquals($expected, $actual, $message);
    }
}