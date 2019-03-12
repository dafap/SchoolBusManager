<?php
/**
 * Calcul des effectifs des élèves transportés et des demandes par classe
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifOrganismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;

class EffectifOrganismes extends AbstractEffectifType1 implements EffectifInterface
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
        return 'organismeId';
    }

    public function demandes($organismeId)
    {
        return StdLib::getParamR([
            'demandes',
            $organismeId
        ], $this->structure, 0);
    }

    public function transportes($organismeId)
    {
        return StdLib::getParamR([
            'transportes',
            $organismeId
        ], $this->structure, 0);
    }
}