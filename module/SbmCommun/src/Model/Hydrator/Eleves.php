<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Eleve dans la table `eleves`
 *
 * Cet hydrator
 * déclaré dans \SbmCommun\Model\Db\Service\TableGateway\TableGatewayEleves::init()
 * et dans \SbmCommun\Model\Db\Service\TableGatewayInvites::init()
 * sera utilisé dans \SbmCommun\Model\Db\Service\Table\Eleves::saveRecord()
 * et dans \SbmCommun\Model\Db\Service\Table\Invites::saveRecord()
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Filter\SansAccent;
use SbmCommun\Model\Db\ObjectData\AbstractObjectData as ObjectData;

class Eleves extends AbstractHydrator
{

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Hydrator\AbstractHydrator::calculate()
     */
    protected function calculate($object)
    {
        if (! $object instanceof ObjectData) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s : On attend un SbmCommun\Model\Db\ObjectData\AbstractObjectData et on a reçu un %s',
                    __METHOD__, gettype($object)));
        }
        $calculate_fields = $object->getCalculateFields();
        $now = new \DateTime('now');
        foreach ($calculate_fields as $value) {
            if (substr($value, - 2) == 'SA') {
                $sa = new SansAccent();
                $index = substr($value, 0, strlen($value) - 2);
                try {
                    $object->$value = $sa->filter($object->$index);
                } catch (\SbmCommun\Model\Db\ObjectData\Exception\ExceptionInterface $e) {}
            } elseif ($value == 'dateModification') {
                $object->dateModification = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'dateCreation') {
                $object->dateCreation = $now->format('Y-m-d H:i:s');
            }
        }
        return $object;
    }
}