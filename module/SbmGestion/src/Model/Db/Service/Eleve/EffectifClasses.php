<?php
/**
 * Calcul des effectifs des élèves transportés et des demandes par classe
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifClasses.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;

class EffectifClasses extends AbstractEffectifType1 implements EffectifInterface
{

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés",
            'demandes' => "nombre de demandes"
        ];
    }

    public function getIdColumn()
    {
        return 'classeId';
    }

    public function demandes($classeId)
    {
        return StdLib::getParamR([
            'demandes',
            $classeId
        ], $this->structure, 0);
    }

    public function transportes($classeId)
    {
        return StdLib::getParamR([
            'transportes',
            $classeId
        ], $this->structure, 0);
    }
}