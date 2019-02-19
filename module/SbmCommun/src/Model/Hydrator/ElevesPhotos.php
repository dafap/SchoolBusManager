<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Photo dans la table ElevesPhotos
 *
 * Cet hydrator, déclaré dans SbmCommun\Model\Db\Service\TableGateway\TableGatewayElevesPhotos::init(), 
 * sera utilisé dans SbmCommun\Model\Db\Service\Table\ElevesPhotos::saveRecord()
 * 
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource ElevesPhotos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 janv. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Model\Db\ObjectData\ElevePhoto as ObjectData;

class ElevesPhotos extends AbstractHydrator
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
                    '%s : On attend un SbmCommun\Model\Db\ObjectData\ElevePhoto et on a reçu un %s',
                    __METHOD__, gettype($object)));
        }
        $calculate_fields = $object->getCalculateFields();
        $now = new \DateTime('now');
        foreach ($calculate_fields as $value) {
            if ($value == 'dateModification') {
                $object->dateModification = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'dateCreation') {
                $object->dateCreation = $now->format('Y-m-d H:i:s');
            }
        }
        return $object;
    }
} 