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
 * @date 27 jan. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;
use SbmCommun\Model\Db\ObjectData\Exception as ObjectDataException;
use SbmBase\Model\DateLib;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Zend\Db\Sql;

class Scolarites extends AbstractSbmTable
{

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
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Table\AbstractTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('joursTransport', new SemaineStrategy());
    }

    /**
     * Renvoie true si l'établissement a changé ou si c'est un nouvel enregistrement ou si district == 0
     *
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        $changeEtab = false;
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $changeEtab = $old_data->district == 0; // pour forcer l'actualisation de district
            $is_new = false;
        } catch (Exception $e) {
            $is_new = true;
        }
        if ($is_new) {
            $obj_data->setCalculateFields(
                [
                    'dateInscription'
                ]);
            $changeEtab = true;
        } else {
            // on vérifie si des données ont changé
            if ($obj_data->isUnchanged($old_data)) {
                return $changeEtab;
            }
            try {
                $changeEtab |= $obj_data->etablissementId != $old_data->etablissementId;
            } catch (ObjectDataException $e) {
                $changeEtab = false;
            }
            $obj_data->addCalculateField('dateModification');
        }
        parent::saveRecord($obj_data);
        return $changeEtab;
    }

    /**
     * Met à jour un ensemble de lignes définies par millesime, eleveId
     *
     * @param int $millesime
     *            Millésime sur lequel on travaille
     * @param array|int $aEleveId
     *            Tableau de eleveId à mettre à jour ou index eleveId à mettre à jour
     * @param bool $paiement
     *            Indique s'il faut valider (true par défaut) ou invalider (false) le paiement
     *            
     * @return int Nombre de lignes mises à jour
     */
    public function setPaiement($millesime, $aEleveId, $paiement = true)
    {
        $where = new Where();
        if (is_array($aEleveId)) {
            $where->equalTo('millesime', $millesime)->in('eleveId', $aEleveId);
        } else {
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $aEleveId);
        }
        $update = $this->table_gateway->getSql()->update();
        $update->set([
            'paiement' => $paiement ? 1 : 0
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
        parent::saveRecord($oData);
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
        parent::saveRecord($oData);
    }

    /**
     * Affecte le champ derogation
     *
     * @param int $millesime            
     * @param int $eleveId            
     * @param int $derogation
     *            0 ou 1
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
        parent::saveRecord($oData);
    }

    /**
     * Renvoie la date de dernière édition des cartes
     */
    public function getLastDateCarte()
    {
        $select = $this->table_gateway->getSql()
            ->select()
            ->columns(
            [
                'lastDateCarte' => new Expression('MAX(dateCarte)')
            ]);
        $rowset = $this->table_gateway->selectWith($select);
        return $rowset->current()->lastDateCarte;
    }

    /**
     * Par défaut, ajoute un duplicata dans le compte des duplicatas de l'élève.
     * Si $cancel, alors retire un duplicata du compte de l'élève.
     *
     * @param int $millesime            
     * @param int $eleveId            
     * @param bool $cancel            
     */
    public function addDuplicata($millesime, $eleveId, $cancel = false)
    {
        $oData = $this->getRecord(
            [
                'millesime' => $millesime,
                'eleveId' => $eleveId
            ]);
        if ($cancel && $oData->duplicata > 0) {
            $oData->duplicata --;
        } else {
            $oData->duplicata ++;
        }
        parent::saveRecord($oData);
    }

    /**
     * Renvoie vrai si la table ne contient pas de données pour ce millésime.
     *
     * @param int $millesime            
     *
     * @return boolean
     */
    public function isEmptyMillesime($millesime)
    {
        $resultset = $this->fetchAll(
            [
                'millesime' => $millesime
            ]);
        return $resultset->count() == 0;
    }

    /**
     * Supprime tous les enregistrements concernant le millesime indiqué.
     *
     * @param unknown $millesime            
     *
     * @return \Zend\Db\TableGateway\int
     */
    public function viderMillesime($millesime)
    {
        return $this->table_gateway->delete(
            [
                'millesime' => $millesime
            ]);
    }
}