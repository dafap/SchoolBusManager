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
 * @date 3 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmGestion\Model\Db\Service\EffectifInterface;
use SbmBase\Model\StdLib;

class EffectifStationsServices extends AbstractEffectifType3 implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $service1Keys = [
            'ligne1Id',
            'sensligne1',
            'moment',
            'ordreligne1'
        ];
        $this->structure = [];
        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['station1Id'] = $this->caractere;

        //$rowset = $this->requete('service1Id', $conditions, 'service1Id');
        $rowset = $this->requete($service1Keys, $conditions, $service1Keys);

        foreach ($rowset as $row) {
            if (array_key_exists('column1', $row) && array_key_exists('column2', $row) &&
                array_key_exists('column3', $row) && array_key_exists('column4', $row)) {
                    $this->structure[$row['column1']][$row['column2']][$row['column3']][$row['column4']][1] = $row['effectif'];
                }
        }

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['a.station2Id'] = $this->caractere;
        $conditions['IsNotNull'] = [
            'a.ligne2Id'
        ];
        $rowset = $this->requetePourCorrespondance('service', $conditions,
            [
                'ligne2Id',
                'sensligne2',
                'moment',
                'ordreligne2'
            ]);
        foreach ($rowset as $row) {
            if (array_key_exists('column1', $row) && array_key_exists('column2', $row) &&
                array_key_exists('column3', $row) && array_key_exists('column4', $row)) {
                    $this->structure[$row['column1']][$row['column2']][$row['column3']][$row['column4']][2] = $row['effectif'];
                }
        }
        // remplace les colonnes 1 et 2 de niveau 5 par leur total
        foreach ($this->structure as &$niveau1) {
            foreach ($niveau1 as &$niveau2) {
                foreach ($niveau2 as &$niveau3) {
                    foreach ($niveau3 as &$niveau4) {
                        $total = 0;
                        foreach ($niveau4 as &$value) {
                            $total += $value;
                        }
                        $niveau4 = $total;
                    }
                }
            }
        }
        return $this->structure;
    }

    /**
     * Surcharge pour gestion de la jointure
     *
     * {@inheritdoc}
     * @see \SbmGestion\Model\Db\Service\Eleve\AbstractEffectifType2::getJointureAffectationsCorrespondances()
     */
    protected function getJointureAffectationsCorrespondances($index)
    {
        return [
            'a.millesime = correspondances.millesime',
            'a.eleveId = correspondances.eleveId',
            'a.trajet = correspondances.trajet',
            'a.jours = correspondances.jours',
            'a.moment = correspondances.moment',
            'a.ligne2Id = correspondances.ligne1Id',
            'a.sensligne2 = correspondances.sensligne1',
            'a.ordreligne2 = correspondances.ordreligne1'
        ];
    }

    /**
     * Surcharge pour gestion des colonnes pour les correspondances
     *
     * {@inheritdoc}
     * @see \SbmGestion\Model\Db\Service\Eleve\AbstractEffectifType2::getKeys()
     */
    protected function getKeys($index)
    {
        return [
            'ligne2Id',
            'sensligne2',
            'moment',
            'ordreligne2'
        ];
    }

    /**
     *
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @return mixed|array
     */
    public function transportes(string $ligneId, int $sens, int $moment, int $ordre)
    {
        return StdLib::getParamR([
            $ligneId,
            $sens,
            $moment,
            $ordre
        ], $this->structure, 0);
    }

    public function getIdColumn()
    {
        return 'serviceId';
    }
}
