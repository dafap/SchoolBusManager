<?php
/**
 * Gestion de la table `tarifs`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Tarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;
use SbmCommun\Model\Strategy\TarifAttributs as TarifAttributsStrategy;

class Tarifs extends AbstractSbmTable
{
    private $modes = array(1 => 'prélèvement', 2 =>'paiement en ligne', 3 => 'chèque ou espèces', 4 => 'par virement');
    private $mode_inconnu = "Le mode fournie est inconnu";
    private $rythmes = array(1 => 'annuel', 2 =>'semestriel', 3 => 'trimestriel', 4 => 'mensuel');
    private $rythme_inconnu = "Le rythme fournie est inconnu";
    private $grilles = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D');
    private $grille_inconnu = "La grille fournie est inconnue";
    
    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'tarifs';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Tarifs';
        $this->id_name = 'tarifId';
    }
    
    /**
     * (non-PHPdoc)
     * @see \SbmCommun\Model\Db\Table\AbstractTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('rythme', new TarifAttributsStrategy($this->rythmes, $this->rythme_inconnu));
        $this->hydrator->addStrategy('grille', new TarifAttributsStrategy($this->grilles, $this->grille_inconnu));
        $this->hydrator->addStrategy('mode', new TarifAttributsStrategy($this->modes, $this->mode_inconnu));
    }
    
    // --------------- nomenclatures ------------------------
    public function getModes()
    {
        return $this->modes;
    }
    
    public function getRythmes() 
    {
        return $this->rythmes;
    }
    
    public function getGrilles()
    {
        return $this->grilles;
    }
    
    // ------------- recherche de données -----------------

    /**
     * La grille 1 contient les tarifs d'inscription
     * La grille 2 contient les tarifs de duplicata
     * Ici, chaque grille ne contient qu'un seul tarif.
     *
     * @param string $choix
     */
    public function getMontant($choix)
    {
        $where = new Where();
        $where->equalTo('grille', $choix == 'inscription' ? 1 : 2);
        $resultset = $this->fetchAll($where);
        $row = $resultset->current();
        return $row->montant;
    }
    public function getTarifId($choix)
    {
        $where = new Where();
        $where->equalTo('grille', $choix == 'inscription' ? 1 : 2);
        $resultset = $this->fetchAll($where);
        $row = $resultset->current();
        return $row->tarifId;
    }
    
    public function setSelection($tarifId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'tarifId' => $tarifId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }
}

