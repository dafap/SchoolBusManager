<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Scolarite dans la table Scolarites
 *
 * Cet hydrator, déclaré dans 
 * SbmCommun\Model\Db\Service\TableGateway\TableGatewayScolarites::init(), 
 * sera utilisé dans SbmCommun\Model\Db\Service\Table\Scolarites::saveRecord()
 * 
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource Scolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 janv. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Model\Db\ObjectData\Scolarite as ObjectData;

class Scolarites extends AbstractHydrator
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
                    '%s : On attend un SbmCommun\Model\Db\ObjectData\Scolarite et on a reçu un %s',
                    __METHOD__, gettype($object)));
        }
        $calculate_fields = $object->getCalculateFields();
        $now = new \DateTime('now');
        foreach ($calculate_fields as $value) {
            if ($value == 'dateModification') {
                $object->dateModification = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'dateInscription') {
                $object->dateInscription = $now->format('Y-m-d H:i:s');
            }
        }
        return $object;
    }
}
 