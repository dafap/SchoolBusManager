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
 * @date 10 mars 2017
 * @version 2017-2.3.1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Where;
use SbmCommun\Model\Strategy\TarifAttributs as TarifAttributsStrategy;

class Tarifs extends AbstractSbmTable
{

    private $modes = [
        1 => 'prélèvement',
        2 => 'paiement en ligne',
        3 => 'chèque ou espèces',
        4 => 'par virement'
    ];

    private $mode_inconnu = "Le mode fournie est inconnu";

    private $rythmes = [
        1 => 'annuel',
        2 => 'semestriel',
        3 => 'trimestriel',
        4 => 'mensuel'
    ];

    private $rythme_inconnu = "Le rythme fournie est inconnu";

    private $grilles = [
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D'
    ];

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
     *
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
     * Renvoie un Where pour getMontant() et getTarifId() en fonction de $choix
     *
     * La grille 1 contient les tarifs d'inscription
     * - rythme = 1 : Tarif annuel
     * - rythme = 3 : 3e trimestre
     * La grille 2 contient le tarif de duplicata (unique)
     *
     * @param string $choix            
     * @return Zend\Db\Sql\Where
     */
    private function tarification($choix)
    {
        $where = new Where();
        switch ($choix) {
            case 'tarif1':
                $where->literal('grille = 1')->literal('rythme = 1');
                break;
            case 'tarif2':
                $where->literal('grille = 1')->literal('rythme = 3');
                break;
            default: // duplicata
                $where->literal('grille = 2');
                break;
        }
        return $where;
    }

    /**
     * Renvoie le montant d'un tarif
     *
     * @param string $choix            
     *
     * @return float (currency)
     */
    public function getMontant($choix)
    {
        $resultset = $this->fetchAll($this->tarification($choix));
        $row = $resultset->current();
        return $row->montant;
    }

    /**
     * Renvoie l'identifiant d'un tarif
     *
     * @param string $choix            
     *
     * @return integer
     */
    public function getTarifId($choix)
    {
        $resultset = $this->fetchAll($this->tarification($choix));
        $row = $resultset->current();
        return $row->tarifId;
    }
    
    /**
     * Renvoie un tableau indexé [tarifId => montant, ...]
     * 
     * @return array
     */
    public function getTarifs()
    {
        $where = new Where();
        $where->literal('grille = 1');
        $resultset = $this->fetchAll($where);
        $result = [];
        foreach ($resultset as $row) {
            $result[$row->tarifId] = $row->montant;
        }
        return $result;
    }

    /**
     * Mise à jour de la colonne `selection`
     *
     * @param int $tarifId            
     * @param byte $selection            
     */
    public function setSelection($tarifId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'tarifId' => $tarifId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }
}

