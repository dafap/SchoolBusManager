<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Eleve dans la table `eleves`
 *
 * Cet hydrator, déclaré dans 
 * SbmCommun\Model\Db\Service\TableGateway\TableGateway::init(),
 * sera utilisé dans SbmCommun\Model\Db\Service\Table\Eleves::saveRecord()
 * 
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 janv. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Filter\SansAccent;
use SbmCommun\Model\Db\ObjectData\Eleve as ObjectData;

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
                    '%s : On attend un SbmCommun\Model\Db\ObjectData\Eleve et on a reçu un %s',
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
                } catch (\SbmCommun\Model\Db\ObjectData\Exception $e) {}
            } elseif ($value == 'dateModification') {
                $object->dateModification = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'dateCreation') {
                $object->dateCreation = $now->format('Y-m-d H:i:s');
            }
        }
        return $object;
    }
}