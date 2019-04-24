<?php
/**
 * Calcul des effectifs des élèves transportés par Service pour un établissement donné.
 *
 * L'initialisation doit nécessairement se faire par :
 *   $objet->setCaractereConditionnel($etablissementId)->init($sanspreinscrits);
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifEtablissementsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmGestion\Model\Db\Service\EffectifInterface;

class EffectifEtablissementsServices extends AbstractEffectifType3 implements
    EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['etablissementId'] = $this->caractere;

        $rowset = $this->requete('service1Id', $conditions, 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $this->structure[$row['service1Id']][1] = $row['effectif'];
        }

        $rowset = $this->requetePourCorrespondance('service', $conditions, 'a.service2Id');
        foreach ($rowset as $row) {
            if ($row['service2Id'] == '')
                continue;
            $this->structure[$row['service2Id']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
        return $this->structure;
    }

    public function getIdColumn()
    {
        return 'serviceId';
    }
}