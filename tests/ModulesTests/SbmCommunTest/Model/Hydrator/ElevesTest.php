<?php
/**
 * Test de fonctionnement d'un SbmCommun\Model\Hydrator\Eleves
 *
 * (utilise un TestAsset\HydratorNeutre dérivé de AbstractHydrator)
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Hydrator
 * @filesource ElevesTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Hydrator;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Db\ObjectData\Eleve as ObjectData;
use SbmCommun\Model\Hydrator\Eleves as Hydrator;

class ElevesTest extends PHPUnit_Framework_TestCase
{

    public function testSA()
    {
        $object = new ObjectData();
        $object->exchangeArray([
            'nom' => 'Délas',
            'prenom' => 'Stéphane'
        ]);
        $object->setArrayMask([
            'nom',
            'prenom',
            'nomSA',
            'prenomSA'
        ]);
        $object->setCalculateFields([
            'nomSA',
            'prenomSA'
        ]);
        
        $hydrator = new Hydrator();
        $data = $hydrator->extract($object);
        $this->assertArrayHasKey('nomSA', $data);
        if (array_key_exists('nomSA', $data)) {
            $this->assertEquals('Delas', $data['nomSA']);
        }
        $this->assertArrayHasKey('prenomSA', $data);
        if (array_key_exists('prenomSA', $data)) {
            $this->assertEquals('Stephane', $data['prenomSA']);
        }
    }

    public function testDataCreation()
    {
        $object = new ObjectData();
        $object->exchangeArray([
            'nom' => 'Tartempion',
            'prenom' => 'Marius'
        ]);
        $object->setArrayMask([
            'nom',
            'prenom',
            'dateCreation'
        ]);
        $object->addCalculateField('dateCreation');
        $hydrator = new Hydrator();
        $data = $hydrator->extract($object);
        $this->assertArrayHasKey('dateCreation', $data);
        if (array_key_exists('dateCreation', $data)) {
            $now = new \DateTime('now');
            $this->assertEquals($now->format('Y-m-d H:i:s'), $data['dateCreation']);
        }
    }

    public function testDateModification()
    {
        $object = new ObjectData();
        $object->exchangeArray([
            'nom' => 'Tartempion',
            'prenom' => 'Marius'
        ]);
        $object->setArrayMask([
            'nom',
            'prenom',
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
        $object->exchangeArray([
            'nom' => 'Tartempion',
            'prenom' => 'Marius',
            'dateModification' => $ancienne_date
        ]);
        $object->setArrayMask([
            'nom',
            'prenom',
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