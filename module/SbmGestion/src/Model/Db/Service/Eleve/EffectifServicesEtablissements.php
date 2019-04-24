<?php
/**
 * Calcul des effectifs des élèves transportés par établissement pour un service donné.
 *
 * L'initialisation doit nécessairement se faire par :
 *   $objet->setCaractereConditionnel($serviceId)->init($sanspreinscrits);
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifServicesEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmGestion\Model\Db\Service\EffectifInterface;

class EffectifServicesEtablissements extends AbstractEffectifType3 implements
    EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['service1Id'] = $this->caractere;
        $rowset = $this->requete('service1Id', $conditions, 'etablissementId');
        foreach ($rowset as $row) {
            $this->structure[$row['etablissementId']][1] = $row['effectif'];
        }

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['a.service2Id'] = $this->caractere;
        $rowset = $this->requetePourCorrespondance('service', $conditions,
            'etablissementId');
        foreach ($rowset as $row) {
            $this->structure[$row['etablissementId']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
        return $this->structure;
    }

    public function getIdColumn()
    {
        return 'etablissementId';
    }
}