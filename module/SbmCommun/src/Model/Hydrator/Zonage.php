<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Zonage dans la table `zonage`
 *
 * Cet hydrator
 * déclaré dans \SbmCommun\Model\Db\Service\TableGateway\TableGateway::init()
 * sera utilisé dans \SbmCommun\Model\Db\Service\Table\Zonage::saveRecord()
 *
 * @project sbm
 * @package SbmCommun/src/Model/Hydrator
 * @filesource Zonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 juin 2020
 * @version 2020-2.5.7
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Filter\ZoneAdresse;
use SbmCommun\Model\Db\ObjectData\Zonage as ObjectData;

class Zonage extends AbstractHydrator
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
                    '%s : On attend un %s et on a reçu un %s',
                    __METHOD__, ObjectData::class, gettype($object)));
        }
        $calculate_fields = $object->getCalculateFields();
        foreach ($calculate_fields as $value) {
            if (substr($value, - 2) == 'SA') {
                $za = new ZoneAdresse();
                $index = substr($value, 0, strlen($value) - 2);
                try {
                    $object->$value = strtoupper($za->filter($object->$index));
                } catch (\SbmCommun\Model\Db\ObjectData\Exception\ExceptionInterface $e) {}
            }
        }
        return $object;
    }
}
