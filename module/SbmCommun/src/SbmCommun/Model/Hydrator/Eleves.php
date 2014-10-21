<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Eleve dans la table `eleves`
 *
 * Cet hydrator, déclaré dans SbmCommun\Model\Db\Service\Table\Eleves::init(),
 * sera utilisé dans SbmCommun\Model\Db\Service\Table\Eleves::saveRecord()
 * 
 * @project sbm
 * @package SbmCommun/Model/Hydrator
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 oct. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Hydrator;

use SbmCommun\Model\Db\ObjectData\Eleve as ObjectData;
use SbmCommun\Filter\SansAccent;

class Eleves extends AbstractHydrator
{
    public function extract($object)
    {
        if (! $object instanceof ObjectData) {
            throw new Exception\InvalidArgumentException(sprintf('%s : On attend un SbmCommun\Model\Db\ObjectData\Eleve et on a reçu un %s', __METHOD__, gettype($object)));
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
                $this->object->$value = $sa->filter($this->object->$index);
            } elseif ($value == 'dateModification') {
                $this->object->dateModification = $now->format('Y-m-d H:i:s');
            } elseif ($value == 'dateCreation') {
                $this->object->dateCreation = $now->format('Y-m-d H:i:s');
            }
        }
    }
}