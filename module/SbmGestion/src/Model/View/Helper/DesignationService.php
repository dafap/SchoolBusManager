<?php
/**
 * Aide de vue permettant d'afficher la désignation d'un service
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmGestion/Model/View/Helper
 * @filesource DesignationService.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\View\Helper;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Stdlib\ArrayObject;

class DesignationService extends AbstractHelper
{
    use \SbmCommun\Model\Traits\ServiceTrait;

    /**
     * Renvoie la désignation d'un service : quelque chose comme '233 Aller Matin N°1'
     *
     * @param ObjectDataInterface|ArrayObject|array $objectdata
     * @param int|string $n
     *            1 ou 2 ou chaine vide
     * @param string $prefixe
     *            préfixe des champs de la table affectation dans la requête
     * @return string
     */
    public function __invoke($objectdata, $n = '', string $prefixe = '')
    {
        if ($prefixe) {
            $prefixe = rtrim($prefixe, '.') . '.';
        }
        if ($objectdata instanceof ObjectDataInterface) {
            $data = [
                'ligneId' => $objectdata->{$prefixe . 'ligne' . $n . 'Id'},
                'sens' => $objectdata->{$prefixe . ($n ? 'sensligne' . $n : 'sens')},
                'moment' => $objectdata->{$prefixe . 'moment'},
                'ordre' => $objectdata->{$prefixe . ($n ? 'ordreligne' . $n : 'ordre')}
            ];
        } else {
            $data = [
                'ligneId' => $objectdata[$prefixe . 'ligne' . $n . 'Id'],
                'sens' => $objectdata[$prefixe . ($n ? 'sensligne' . $n : 'sens')],
                'moment' => $objectdata[$prefixe . 'moment'],
                'ordre' => $objectdata[$prefixe . ($n ? 'ordreligne' . $n : 'ordre')]
            ];
        }
        return $this->identifiantService($data);
    }
}