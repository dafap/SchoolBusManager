<?php
/**
 * Hydrator pour tenir à jour la modification d'une fiche Responsable dans la table Responsables
 * 
 * Cet hydrator, déclaré dans SbmCommun\Model\Db\Service\Table\Responsables::init(), 
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
            /*if ($value == 'demenagement') {
                if ($this->object->demenagement) {
                    $this->object->ancienAdresseL1 = $this->object->adresseL1;
                    $this->object->ancienAdresseL2 = $this->object->adresseL2;
                    $this->object->ancienCodePostal = $this->object->codePostal;
                    $this->object->ancienCommuneId = $this->object->communeId;
                    $this->object->dateDemenagement = $now->format('Y-m-d');
                } else {
                    $this->object->ancienAdresseL1 = null;
                    $this->object->ancienAdresseL2 = null;
                    $this->object->ancienCodePostal = null;
                    $this->object->ancienCommuneId = null;
                    $this->object->dateDemenagement = null;
                }
            } else*/if (substr($value, - 2) == 'SA') {
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