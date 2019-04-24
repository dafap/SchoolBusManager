<?php
/**
 * Calcul des effectifs des élèves transportés par Station
 *
 * On peut obtenir le résultat total ou le résultat pour le responsable1 ou pour le responsable2
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;

class EffectifStations extends AbstractEffectifType2 implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        $rowset = $this->requete('station1Id', $this->getConditions($sanspreinscrits),
            'station1Id');
        foreach ($rowset as $row) {
            $this->structure[$row['column']][1] = $row['effectif'];
        }
        $filtre = $this->getConditions($sanspreinscrits);
        $filtre['isNotNull'] = [
            'a.service2Id'
        ];
        $rowset = $this->requetePourCorrespondance('station', $filtre, 'station2Id');
        foreach ($rowset as $row) {
            $this->structure[$row['column']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
    }

    /**
     *
     * @param int $stationId
     */
    public function transportes($stationId)
    {
        return StdLib::getParam($stationId, $this->structure, 0);
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés"
        ];
    }

    public function getIdColumn()
    {
        return 'stationId';
    }
}