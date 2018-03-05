<?php
/**
 * Gestion de la table `eleves`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mars 2018
 * @version 2018-2.3.19
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\PredicateSet;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Db\ObjectData\Exception as ExceptionObjectData;

class Eleves extends AbstractSbmTable
{

    /**
     * Renvoie l'enregistrement corresponsdant au gid donné
     *
     * @param int $gid            
     * @throws Exception
     * @return mixed
     */
    public function getRecordByGid($gid)
    {
        $array_where = array(
            'id_ccda = ?' => $gid
        );
        $condition_msg = "id_ccda = $gid";
        
        $rowset = $this->table_gateway->select($array_where);
        $row = $rowset->current();
        if (! $row) {
            throw new Exception(sprintf(_("Could not find row '%s' in table %s"), $condition_msg, $this->table_name));
        }
        return $row;
    }

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'eleves';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Eleves';
        $this->id_name = 'eleveId';
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        $dateNUnchanged = true;
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (Exception $e) {
            try {
                $nom = $obj_data->nom;
                $prenom = $obj_data->prenom;
                $dateN = $obj_data->dateN;
                $responsable1Id = $obj_data->responsable1Id;
                $responsable2Id = $obj_data->responsable2Id;
                $old_data = $this->getByIdentite($nom, $prenom, $dateN, $responsable1Id);
                if (! $old_data) {
                    $old_data = $this->getByIdentite($nom, $prenom, $dateN, $responsable2Id);
                }
                $is_new = ! $old_data;
                if (! $is_new) {
                    if ($old_data->dateN == '1950-01-01') {
                        $old_data->dateN = $obj_data->dateN;
                        $dateNUnchanged = false;
                    }
                    $obj_data = $old_data;
                    // remettre les responsables dans l'ordre demandé cette année
                    $obj_data->responsable1Id = $responsable1Id;
                    $obj_data->responsable2Id = $responsable2Id;
                }
            } catch (ExceptionObjectData $e) {
                // die($e->getMessage());
                $is_new = true;
            }
        }
        if ($is_new) {
            $obj_data->setCalculateFields(array(
                'nomSA',
                'prenomSA',
                'dateCreation'
            ));
            for ($u = $obj_data->createNumero(), $i = 0; $this->numeroOccupe($u); $i ++) {
                $u ++;
                $u += 2 * $i;
                $u %= $obj_data::BASE;
                if ($u == 0)
                    $u = $obj_data::BASE;
            }
            $obj_data->numero = $u;
        } else {
            // on vérifie si des données ont changé
            if ($dateNUnchanged && $obj_data->isUnchanged($old_data))
                return;
            if (! $obj_data->isUnchanged($old_data)) {
                if ($old_data->nom != $obj_data->nom) {
                    $obj_data->addCalculateField('nomSA');
                }
                if ($old_data->prenom != $obj_data->prenom) {
                    $obj_data->addCalculateField('prenomSA');
                }
            }
            $obj_data->addCalculateField('dateModification');
        }
        parent::saveRecord($obj_data);
        if ($is_new) {
            return $this->getTableGateway()->getLastInsertValue();
        } else {
            return $obj_data->eleveId;
        }
    }

    /**
     * Marque la fiche sélectionnée ou non sélectionnée selon la valeur de $selection
     *
     * @param int $eleveId            
     * @param bool $selection
     *            0 ou 1
     */
    public function setSelection($eleveId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'eleveId' => $eleveId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }

    /**
     * Marque le champ `mailchimp` de la valeur de $mailchimp
     *
     * @param int $eleveId            
     * @param bool $mailchimp
     *            0 ou 1
     */
    public function setMailchimp($eleveId, $mailchimp)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'eleveId' => $eleveId,
            'mailchimp' => $mailchimp
        ));
        parent::saveRecord($oData);
    }

    /**
     * Vérifie si un numero est occupé.
     * Renvoie vrai s'il est occupé.
     *
     * @param int $n            
     * @return boolean
     */
    private function numeroOccupe($n)
    {
        $where = new Where();
        $where->equalTo('numero', $n);
        return $this->fetchAll($where)->count() != 0;
    }

    /**
     * Liste des élèves ayant la personne d'identifiant $responsableId comme responsable (1, 2 ou financier)
     *
     * @param long $responsableId            
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function duResponsable($responsableId)
    {
        $where = new Where();
        $where->equalTo('responsable1Id', $responsableId)->OR->equalTo('responsable2Id', $responsableId)->OR->equalTo('responsableFId', $responsableId);
        return $this->fetchAll($where, array(
            'nom',
            'prenom'
        ));
    }

    /**
     * Liste des élèves ayant comme responsable 1 la personne d'identifiant $responsableId
     *
     * @param long $responsableId            
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function duResponsable1($responsableId)
    {
        $where = new Where();
        $where->equalTo('responsable1Id', $responsableId);
        return $this->fetchAll($where, array(
            'nom',
            'prenom'
        ));
    }

    /**
     * Liste des élèves ayant comme responsable 2 la personne d'identifiant $responsableId
     *
     * @param long $responsableId            
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function duResponsable2($responsableId)
    {
        $where = new Where();
        $where->equalTo('responsable2Id', $responsableId);
        return $this->fetchAll($where, array(
            'nom',
            'prenom'
        ));
    }

    /**
     * Liste des élèves ayant comme responsable financier la personne d'identifiant $responsableId
     *
     * @param long $responsableId            
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function duResponsableFinancier($responsableId)
    {
        $where = new Where();
        $where->equalTo('responsableFId', $responsableId);
        return $this->fetchAll($where, array(
            'nom',
            'prenom'
        ));
    }

    /**
     * On cherche un élève connaissant :
     * 1/ son nom, son prenom et sa date de naissance
     * 2/ son nom, son prenom et son responsable1Id
     * 3/ son nom, son prenom et son responsable2Id
     * La recherche s'effectue dans cet ordre s'arrête dès qu'on a trouvé.
     *
     * Renvoie un SbmCommun\Model\Db\ObjectDataInterface ou false s'il n'est pas trouvé.
     * Renvoie false si l'un des paramètres est vide.
     *
     * @param string $nom            
     * @param string $prenom            
     * @param string $dateN            
     * @param int $responsableId            
     *
     * @return boolean|\Zend\Db\ResultSet\HydratingResultSet
     */
    private function getByIdentite($nom, $prenom, $dateN, $responsableId)
    {
        if (empty($nom) || empty($prenom) || (empty($dateN) && empty($responsableId))) {
            return false;
        }
        $resultset = $this->fetchAll(array(
            'nom' => $nom,
            'prenom' => $prenom,
            'dateN' => $dateN
        ));
        if ($resultset->count() == 0) {
            $resultset = $this->fetchAll(array(
                'nom' => $nom,
                'prenom' => $prenom,
                'responsable1Id' => $responsableId
            ));
        }
        if ($resultset->count() == 0) {
            $resultset = $this->fetchAll(array(
                'nom' => $nom,
                'prenom' => $prenom,
                'responsable2Id' => $responsableId
            ));
        }
        return $resultset->current();
    }
}