<?php
/**
 * Calcul des effectifs des services pour un transporteur
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Service
 * @filesource EffectifTransporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Service;

use SbmGestion\Model\Db\Service\EffectifInterface;

class EffectifTransporteurs extends AbstractEffectif implements EffectifInterface
{

    public function getIdColumn()
    {
        return 'transporteurId';
    }
}