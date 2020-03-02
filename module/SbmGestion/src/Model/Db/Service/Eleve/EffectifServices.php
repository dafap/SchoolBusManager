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
 * @date 2 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;

class EffectifServices extends AbstractEffectifType2 implements EffectifInterface
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
        $rowset = $this->requete($service1Keys, $this->getConditions($sanspreinscrits),
            $service1Keys);
        /*
         * la requête est correcte et renvoie un ensemble d'enregistrements de la forme
         * effectif column1 columne2 column3 column4 où column1 correspond à ligne1Id,
         * comumn2 à sensligne1 ...
         */

        foreach ($rowset as $row) {
            $this->structure[$row['column1']][$row['column2']][$row['column3']][$row['column4']][1] = $row['effectif'];
        }
        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['IsNotNull'] = ['a.ligne2Id'];
        $rowset = $this->requetePourCorrespondance('service',
            $conditions,
            [
                'ligne2Id',
                'sensligne2',
                'moment',
                'ordreligne2'
            ]);
        foreach ($rowset as $row) {
            echo '<p>' . $row['column1'] . '<br>' . $row['column2'] . '<br>' .
                $row['column3'] . '<br>' . $row['column4'] . '<br>' . $row['effectif'] .
                '</p>';
            $this->structure[$row['column1']][$row['column2']][$row['column3']][$row['column4']][2] = $row['effectif'];
        }
        // remplace les colonnes 1 et 2 de niveau 5 par leur total
        //echo '<pre>';
        //print_r($this->structure);
        //echo '</pre>';
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
     * @param string $serviceId
     */

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

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés"
        ];
    }

    public function getIdColumn()
    {
        return [
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ];
    }
}