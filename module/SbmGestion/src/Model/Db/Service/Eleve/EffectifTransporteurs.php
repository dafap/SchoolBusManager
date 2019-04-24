<?php
/**
 * Calcul des effectifs des élèves transportés par Transporteur
 *
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifTransporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;

class EffectifTransporteurs extends AbstractEffectifType4 implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        // pour compter les transporteurs associés au service1Id dans les affectations
        $rowset = $this->requete($this->getConditions($sanspreinscrits));
        foreach ($rowset as $row) {
            $this->structure[$row[$this->getIdColumn()]][1] = $row['effectif'];
        }
        // pour compter les transporteurs associés au service2Id dans les affectations
        // quand c'est nécessaire
        $rowset = $this->requetePourCorrespondance($this->getConditions($sanspreinscrits));
        foreach ($rowset as $row) {
            $this->structure[$row[$this->getIdColumn()]][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
        return $this->structure;
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés"
        ];
    }

    public function getIdColumn()
    {
        return 'transporteurId';
    }

    /**
     *
     * @param int $transporteurId
     *
     * @return mixed|array
     */
    public function transportes($transporteurId)
    {
        return StdLib::getParam($transporteurId, $this->structure, 0);
    }
}