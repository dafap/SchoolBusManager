<?php
/**
 * Gestion de la table `scolarites`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Scolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Scolarites extends AbstractSbmTable
{
    use OutilsMillesimeTrait;

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'scolarites';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Scolarites';
        $this->id_name = [
            'millesime',
            'eleveId'
        ];
        $this->strategies['joursTransportR1'] = new SemaineStrategy();
        $this->strategies['joursTransportR2'] = new SemaineStrategy();
    }

    /**
     * Renvoie true si c'est un nouvel enregistrement ou si l'enregistrement précédents
     * n'avait pas les droits (district = 0 ou distances < 1 km) ou si les éléments de
     * détermination des droits et tarifs ont changé (etablissement, regime) (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     *
     * @return array tableau de booléens dont les clés sont 'etablissementChange',
     *         'reductionChange' et 'saveResultat'
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            // résultat type
            $obj_complete = $this->getObjData()->exchangeArray(
                array_merge($old_data->getArrayCopy(), $obj_data->getArrayCopy()));
            $result = [
                'is_new' => false,
                'distanceR1Inconnue' => $obj_complete->demandeR1 &&
                ($obj_complete->distanceR1 == 99 || $obj_complete->distanceR1 == 0),
                'distanceR2Inconnue' => $obj_complete->demandeR2 &&
                ($obj_complete->distanceR2 == 99 || $obj_complete->distanceR2 == 0),
                'etablissementChange' => false,
                'gaChange' => false,
                'reductionChange' => false,
                'saveRecord' => null,
                'obj_data' => $obj_data
            ];
            // update
            if ($old_data->etablissementId != $obj_complete->etablissementId) {
                $obj_data->distanceR1 = $obj_data->distanceR2 = 0;
                if ($obj_complete->demandeR1 == 2) {
                    $obj_data->demandeR1 = 1;
                }
                if ($obj_complete->demandeR2 == 2) {
                    $obj_data->demandeR2 = 1;
                }
                $result['etablissementChange'] = true;
            }
            if ($old_data->derogation != $obj_complete->derogation) {
                $obj_data->reductionR1 = ($obj_complete->derogation != 0) ? 1 : 0;
                $obj_data->reductionR2 = $obj_data->reductionR1;
                $result['reductionChange'] = true;
            }
            if (($old_data->demandeR2 + $obj_complete->demandeR2) > 0 &&
                ($old_data->demandeR2 * $obj_complete->demandeR2) == 0) {
                $obj_data->addCalculateField('dateDemandeR2');
                $result['gaChange'] = true;
            }
            $obj_data->addCalculateField('dateModification');
        } catch (Exception\ExceptionInterface $e) {
            // insert
            $result = [
                'is_new' => true,
                'distanceR1Inconnue' => true,
                'distanceR2Inconnue' => true,
                'etablissementChange' => true,
                'gaChange' => true,
                'reductionChange' => true,
                'saveRecord' => null,
                'obj_data' => $obj_data
            ];
            if (isset($obj_data->demandeR1) && isset($obj_data->distanceR1)) {
                $result['distanceR1Inconnue'] = $obj_data->demandeR1 &&
                    ($obj_data->distanceR1 == 99 || $obj_data->distanceR1 == 0);
            }
            if (isset($obj_data->demandeR2) && isset($obj_data->distanceR2)) {
                $result['distanceR2Inconnue'] = $obj_data->demandeR2 &&
                    ($obj_data->distanceR2 == 99 || $obj_data->distanceR2 == 0);
            }
            $obj_data->setCalculateFields([
                'dateInscription'
            ]);
            try {
                if ($obj_data->demandeR2) {
                    $obj_data->addCalculateField('dateDemandeR2');
                }
            } catch (\Exception $e) {
            }
        }
        $result['saveRecord'] = parent::saveRecord($obj_data);
        return $result;
    }

    /**
     * Met à jour un ensemble de lignes définies par millesime, eleveId
     *
     * @param int $millesime
     *            Millésime sur lequel on travaille
     * @param array|int $aEleveId
     *            Tableau de eleveId à mettre à jour ou index eleveId à mettre à jour
     * @param string $r
     *            prend la valeur R1 ou R2
     * @param bool $paiement
     *            Indique s'il faut valider (true par défaut) ou invalider (false) le
     *            paiement
     * @return int Nombre de lignes mises à jour
     */
    public function setPaiement(int $millesime, $aEleveId, int $r = 1,
        bool $paiement = true)
    {
        $where = new Where();
        if (empty($aEleveId)) {
            return 0;
        } elseif (is_array($aEleveId)) {
            $where->equalTo('millesime', $millesime)->in('eleveId', $aEleveId);
        } else {
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $aEleveId);
        }
        $update = $this->table_gateway->getSql()->update();
        $update->set([
            'paiement' . $r => $paiement ? 1 : 0
        ])->where($where);
        return $this->table_gateway->updateWith($update);
    }

    /**
     * Coche ou décoche le champ accordRi (i=1 ou 2)
     *
     * @param int $millesime
     *            Millesime sur lequel on travaille
     * @param int $eleveId
     *            identifiant d'un eleve
     * @param string $r
     *            R1 ou R2
     * @param bool $accord
     *            0 ou 1
     */
    public function setAccord($millesime, $eleveId, $r, $accord)
    {
        $champ = "accord$r";
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'millesime' => $millesime,
                'eleveId' => $eleveId,
                $champ => $accord
            ]);
        return parent::saveRecord($oData);
    }

    /**
     * Affecte le champ inscrit
     *
     * @param int $millesime
     * @param int $eleveId
     * @param int $inscrit
     *            0 (rayer) ou 1 (inscrit)
     */
    public function setInscrit($millesime, $eleveId, $inscrit)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'millesime' => $millesime,
                'eleveId' => $eleveId,
                'inscrit' => $inscrit
            ]);
        return parent::saveRecord($oData);
    }

    /**
     * Affecte le champ derogation
     *
     * @param int $millesime
     * @param int $eleveId
     * @param int $derogation
     *            0 ou 1 ou 2
     */
    public function setDerogation($millesime, $eleveId, $derogation)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'millesime' => $millesime,
                'eleveId' => $eleveId,
                'derogation' => $derogation
            ]);
        return parent::saveRecord($oData);
    }

    /**
     * Renvoie la date de dernière édition des cartes
     */
    public function getLastDateCarte()
    {
        $select = $this->table_gateway->getSql()
            ->select()
            ->columns([
            'lastDateCarte' => new Expression('MAX(dateCarteR1)')
        ]);
        $rowset = $this->table_gateway->selectWith($select);
        return $rowset->current()->lastDateCarte;
    }

    /**
     * Par défaut, ajoute un duplicata dans le compte des duplicatas de l'élève. Si
     * $cancel, alors retire un duplicata du compte de l'élève.
     *
     * @param int $millesime
     * @param int $eleveId
     * @param bool $cancel
     */
    public function addDuplicata($millesime, $eleveId, $cancel = false)
    {
        $oData = $this->getRecord([
            'millesime' => $millesime,
            'eleveId' => $eleveId
        ]);
        if ($cancel && $oData->duplicataR1 > 0) {
            $oData->duplicataR1 --;
        } else {
            $oData->duplicataR1 ++;
        }
        return parent::saveRecord($oData);
    }
}