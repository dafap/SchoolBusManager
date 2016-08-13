<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Responsable dans 
 * la table `responsables`
 * 
 * Cet hydrator est déclaré dans 
 * SbmCommun\Model\Db\Service\TableGateway\TableGatewayResponsables::init(), 
 * et est utilisé dans SbmCommun\Model\Db\Service\Table\Responsables::saveRecord()
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 août 2016
 * @version 2016-2.1.10
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Model\Db\ObjectData\Responsable as ObjectData;
use SbmCommun\Filter\SansAccent;
use Zend\Authentication\Storage\Session;
use DafapSession\Authentication\AuthenticationService;
use DafapSession\Authentication\AuthenticationServiceFactory;

class Responsables extends AbstractHydrator
{

    /**
     * (non-PHPdoc)
     * 
     * @see \SbmCommun\Model\Hydrator\AbstractHydrator::calculate()
     */
    protected function calculate($object)
    {
        if (! $object instanceof ObjectData) {
            throw new Exception\InvalidArgumentException(sprintf('%s : On attend un SbmCommun\Model\Db\ObjectData\Responsable et on a reçu un %s', __METHOD__, gettype($object)));
        }
        $calculate_fields = $object->getCalculateFields();
        $now = new \DateTime('now');
        foreach ($calculate_fields as $value) {
            if (substr($value, - 2) == 'SA') {
                $sa = new SansAccent();
                $index = substr($value, 0, strlen($value) - 2);
                try {
                    $object->$value = $sa->filter($object->$index);
                } catch (\SbmCommun\Model\Db\ObjectData\Exception $e) {}
            } elseif ($value == 'dateModification') {
                $object->dateModification = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'dateCreation') {
                $object->dateCreation = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'userId') {
                $auth = new AuthenticationService(new Session(AuthenticationServiceFactory::SESSION_AUTH_NAMESPACE));
                $object->userId = $auth->getUserId();
            }
        }
        return $object;
    }
}