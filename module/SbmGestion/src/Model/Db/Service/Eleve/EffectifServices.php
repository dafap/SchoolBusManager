<?php
/**
 * Calcul des effectifs des élèves transportés par Service
 *
 * On peut obtenir le résultat total ou le résultat pour le responsable1 ou pour le responsable2
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;

class EffectifServices extends AbstractEffectifType2 implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        $rowset = $this->requete('service1Id', $this->getConditions($sanspreinscrits),
            'service1Id');
        foreach ($rowset as $row) {
            $this->structure[$row['column']][1] = $row['effectif'];
        }
        $rowset = $this->requetePourCorrespondance('service',
            $this->getConditions($sanspreinscrits), 'service2Id');
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
     * @param string $serviceId
     */
    public function transportes($serviceId)
    {
        return StdLib::getParam($serviceId, $this->structure, 0);
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés"
        ];
    }

    public function getIdColumn()
    {
        return 'serviceId';
    }
}