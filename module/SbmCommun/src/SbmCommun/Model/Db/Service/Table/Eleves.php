<?php
/**
 * Gestion de la table `eleves`
 *
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

class Eleves extends AbstractSbmTable
{
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
    
    /**
     * Liste des élèves ayant la personne d'identifiant $responsableId comme responsable (1, 2 ou financier)
     * 
     * @param long $responsableId
     * @return \SbmCommun\Model\Db\Service\Table\ResultSet
     */
    public function duResponsable($responsableId) 
    {
        $where = new Where();
        $where->equalTo('respId1', $responsableId)->OR->equalTo('respId2', $responsableId)->OR->equalTo('FactId', $responsableId);
        return $this->fetchAll($where, array('nom', 'prenom'));
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
        $where->equalTo('respId1', $responsableId);
        return $this->fetchAll($where, array('nom', 'prenom'));
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
        $where->equalTo('respId2', $responsableId);
        return $this->fetchAll($where, array('nom', 'prenom'));
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
        $where->equalTo('factId', $responsableId);
        return $this->fetchAll($where, array('nom', 'prenom'));
    }
}