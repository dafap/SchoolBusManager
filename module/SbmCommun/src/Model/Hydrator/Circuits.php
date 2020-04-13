<?php
/**
 * Hydrator pour s'assurer qu'à un point d'arrêt l'heure de départ n'est pas antérieure à l'heure d'arrivée.
 *
 * En cas d'erreur, c'est l'horaireA qui fait référence et qui est recopié dans l'horaireD
 *
 * @project sbm
 * @package SbmCommun/src/Model/Hydrator
 * @filesource Circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Hydrator;

class Circuits extends AbstractHydrator
{

    /**
     * La vérification de horaireD n'est faite que si horaireD est dans l'objet
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Hydrator\AbstractHydrator::calculate()
     */
    protected function calculate($object)
    {
        try {
            if (strtotime($object->horaireD) < strtotime($object->horaireA)) {
                $object->horaireD = $object->horaireA;
            }
        } catch (\Exception $e) {
        }
        return $object;
    }
}