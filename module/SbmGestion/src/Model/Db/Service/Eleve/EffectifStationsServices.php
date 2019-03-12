<?php
/**
 * Calcul des effectifs des élèves transportés par Service pour une station donnée.
 *
 * L'initialisation doit nécessairement se faire par :
 *   $objet->setCaractereConditionnel($stationId)->init($sanspreinscrits);
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifStationsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

class EffectifStationsServices extends AbstractEffectifType3 implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['station1Id'] = $this->caractere;
        $rowset = $this->requete('service1Id', $conditions, 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $this->structure[$row['service1Id']][1] = $row['effectif'];
        }

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['a.station2Id'] = $this->caractere;
        $rowset = $this->requetePourCorrespondance('service', $conditions, 'service2Id');
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
