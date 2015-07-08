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
 * @date 18 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\PredicateSet;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\Db\Sql\Zend\Db\Sql;

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

    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (Exception $e) {
            $is_new = true;
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
            if ($obj_data->isUnchanged($old_data))
                return;
            
            if ($old_data->nom != $obj_data->nom) {
                $obj_data->addCalculateField('nomSA');
            }
            if ($old_data->prenom != $obj_data->prenom) {
                $obj_data->addCalculateField('prenomSA');
            }
            $obj_data->addCalculateField('dateModification');
        }        
        parent::saveRecord($obj_data);
    }
    
    public function setSelection($eleveId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array('eleveId' => $eleveId, 'selection' => $selection));
        parent::saveRecord($oData);
    }
    
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
}