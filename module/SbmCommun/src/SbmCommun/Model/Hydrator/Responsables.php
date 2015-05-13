<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Responsable dans la table Responsables
 * 
 * Cet hydrator, déclaré dans SbmCommun\Model\Db\Service\TableGateway\TableGatewayResponsables::init(), 
 * sera utilisé dans SbmCommun\Model\Db\Service\Table\Responsables::saveRecord()
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Model\Db\ObjectData\Responsable as ObjectData;
use SbmCommun\Filter\SansAccent;
use Zend\Authentication\Storage\Session;
use DafapSession\Model\Authentication\AuthenticationService;
use DafapSession\Model\Authentication\AuthenticationServiceFactory;
use DafapSession\Model\DafapSession\Model;

class Responsables extends AbstractHydrator
{

    public function extract($object)
    {
        if (! $object instanceof ObjectData) {
            throw new Exception\InvalidArgumentException(sprintf('%s : On attend un SbmCommun\Model\Db\ObjectData\Responsable et on a reçu un %s', __METHOD__, gettype($object)));
        }
        return parent::extract($object);
    }

    protected function calculate()
    {
        $calculate_fields = $this->object->getCalculateFields();
        $now = new \DateTime('now');
        foreach ($calculate_fields as $value) {
            if (substr($value, - 2) == 'SA') {
                $sa = new SansAccent();
                $index = substr($value, 0, strlen($value) - 2);
                try {
                    $this->object->$value = $sa->filter($this->object->$index);
                } catch (\SbmCommun\Model\Db\ObjectData\Exception $e) {}
            } elseif ($value == 'dateModification') {
                $this->object->dateModification = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'dateCreation') {
                $this->object->dateCreation = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'userId') {               
                $auth = new AuthenticationService(new Session(AuthenticationServiceFactory::SESSION_AUTH_NAMESPACE));
                $this->object->userId = $auth->getUserId();
            }
        }
    }
}