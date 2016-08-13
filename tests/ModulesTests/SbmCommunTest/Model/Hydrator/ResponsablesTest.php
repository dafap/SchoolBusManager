<?php
/**
 * Test de fonctionnement d'un SbmCommun\Model\Hydrator\Responsables
 *
 * (utilise un TestAsset\HydratorNeutre dérivé de AbstractHydrator)
 *
 * @project sbm
 * @package ModulesTests/SbmCommunTest/Model/Hydrator
 * @filesource ResponsablesTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmCommunTest\Model\Hydrator;

use PHPUnit_Framework_TestCase;
use SbmCommun\Model\Db\ObjectData\Responsable as ObjectData;
use SbmCommun\Model\Hydrator\Responsables as Hydrator;

class ResponsablesTest extends PHPUnit_Framework_TestCase
{

    public function testSA()
    {
        $object = new ObjectData();
        $object->exchangeArray([
            'nom' => 'Délas',
            'prenom' => 'Stéphane',
            'nom2' => '',
            'prenom2' => 'Marlène',
            'adresseL1' => 'Rue de l\'église'
        ]);
        $object->setArrayMask([
            'nom',
            'prenom',
            'nom2',
            'prenom2',
            'adresseL1',
            'nomSA',
            'prenomSA',
            'nom2SA',
            'prenom2SA'
        ]);
        $object->setCalculateFields([
            'nomSA',
            'prenomSA',
            'nom2SA',
            'prenom2SA'
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
        $this->assertArrayHasKey('nom2SA', $data);
        if (array_key_exists('nom2SA', $data)) {
            $this->assertEquals('', $data['nom2SA']);
        }
        $this->assertArrayHasKey('prenom2SA', $data);
        if (array_key_exists('prenom2SA', $data)) {
            $this->assertEquals('Marlene', $data['prenom2SA']);
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

    public function testUserId()
    {
        // @todo il faut simuler une session
    }
} 