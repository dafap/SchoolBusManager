<?php
/**
 * Test de fonctionnement d'un SbmCommun\Model\Hydrator\Scolarites
 *
 * (utilise un TestAsset\HydratorNeutre dérivé de AbstractHydrator)
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Hydrator
 * @filesource ScolaritesTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Hydrator;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Db\ObjectData\Scolarite as ObjectData;
use SbmCommun\Model\Hydrator\Scolarites as Hydrator;

class ScolaritesTest extends PHPUnit_Framework_TestCase
{

    public function testDataInscription()
    {
        $object = new ObjectData();
        $object->exchangeArray([
            'millesime' => 2016,
            'eleveId' => 233
        ]);
        $object->setArrayMask([
            'millesime',
            'eleveId',
            'dateInscription'
        ]);
        $object->addCalculateField('dateInscription');
        $hydrator = new Hydrator();
        $data = $hydrator->extract($object);
        $this->assertArrayHasKey('dateInscription', $data);
        if (array_key_exists('dateInscription', $data)) {
            $now = new \DateTime('now');
            $this->assertEquals($now->format('Y-m-d H:i:s'), $data['dateInscription']);
        }
    }

    public function testDateModification()
    {
        $object = new ObjectData();
        $object->exchangeArray([
            'millesime' => 2016,
            'eleveId' => 233
        ]);
        $object->setArrayMask([
            'millesime',
            'eleveId',
            'dateModification'
        ]);
        $object->addCalculateField('dateModification');
        $hydrator = new Hydrator();
        $data = $hydrator->extract($object);
        $this->assertArrayHasKey('dateModification', $data);
        if (array_key_exists('dateModification', $data)) {
            $now = new \DateTime('now');
            $this->assertEquals($now->format('Y-m-d H:i:s'), $data['dateModification']);
        }
    }

    public function testNouvelleDateModification()
    {
        $ancienne_date = '2016-06-01 10:23:11';
        $object = new ObjectData();
        $object->exchangeArray(
            [
                'millesime' => 2016,
                'eleveId' => 233,
                'dateModification' => $ancienne_date
            ]);
        $object->setArrayMask([
            'millesime',
            'eleveId',
            'dateModification'
        ]);
        $object->addCalculateField('dateModification');
        $hydrator = new Hydrator();
        $data = $hydrator->extract($object);
        $this->assertArrayHasKey('dateModification', $data);
        if (array_key_exists('dateModification', $data)) {
            $now = new \DateTime('now');
            $this->assertNotEquals($ancienne_date, $data['dateModification']);
            $this->assertEquals($now->format('Y-m-d H:i:s'), $data['dateModification']);
        }
    }
}