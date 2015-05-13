<?php
/**
 * Gestion de la table système `documents`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource Calendar.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 nov. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Exception;
use Zend\Db\Sql\Where;
use DateTime;
use Zend\Db\Sql\Expression;

class Calendar extends AbstractSbmTable
{
    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'calendar';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\Calendar';
        $this->id_name = 'calendarId';
    }
    
    
    /**
     * Renvoie la liste des années scolaires
     * 
     * @return array
     *  Toutes les colonnes de la table sont renvoyées en filtrant les lignes dont la nature est 'AS'
     */
    public function getAnneesScolaires()
    {
        $resultset = $this->fetchAll("nature = 'AS'", 'millesime DESC');
        if (! $resultset->count()) {
            throw new Exception('Les années scolaires ne sont pas définies. Il faut initialiser à nouveau la table système `calendar`.');
        }
        $result = array();
        foreach ($resultset as $row) {
            $ligne = $row->getArrayCopy();
            $ligne['valid'] = $this->isValidMillesime($ligne['millesime']);
            $result[] = $ligne;
        }
        return $result;
    }
    
    public function getAnneeScolaire($millesime)
    {
        $resultset = $this->fetchAll("nature = 'AS' AND millesime = $millesime");
        if (! $resultset->count()) {
            throw new Exception(sprintf("L'année scolaire %4d-%4d n'est pas définie.", $millesime, $millesime + 1));
        }
        return $resultset->current()->getArrayCopy();
    }
    
    public function getMillesime($millesime)
    {
        $resultset = $this->fetchAll("millesime = $millesime", 'ordinal');
        if (! $resultset->count()) {
            throw new Exception(sprintf("L'année scolaire %4d-%4d n'est pas définie.", $millesime, $millesime + 1));
        }
        $result = array();
        foreach ($resultset as $row) {
            $result[] = $row->getArrayCopy();
        }
        return $result;
    }
    
    /**
     * Renvoie le plus grand millesime utilisé
     * 
     * @return int
     */
    public function getDernierMillesime()
    {
        $select = $this->getTableGateway()->getSql()->select();
        $select->columns(array('millesime' => new Expression('max(millesime)')));
        $resultset = $this->getTableGateway()->selectWith($select);
        $row = $resultset->current();
        return $row->millesime;
    }
    
    /**
     * Renvoie le plus grand millesime valide
     *
     * @return int
     */
    public function getDefaultMillesime()
    {
        $where1 = new Where();
        $where1->isNull('dateDebut')->OR->isNull('dateFin')->OR->isNull('echeance');
        $select1 = $this->getTableGateway()->getSql()->select();
        $select1->columns(array('millesime'))->where($where1);
        
        $where  = new Where();
        $where->literal('ouvert = 1')->notIn('millesime', $select1);
        $select = $this->getTableGateway()->getSql()->select()->columns(array('millesime' => new Expression('max(millesime)')))->where($where);
        $resultset = $this->getTableGateway()->selectWith($select);
        $row = $resultset->current();
        return $row->millesime;
    }
    
    /**
     * Vérifie si la colonne date précisée ne contient pas de valeur NULL pour le millesime indiqué.
     * 
     * @param int millesime
     *  Millesime à vérifier
     * @param string $column
     *  Nom de la colonne
     *  
     *  @return bool
     */
    private function isValidColDate($millesime, $column)
    {
        $where = new Where();
        $where->equalTo('millesime', $millesime)->isNull($column);
        $select = $this->getTableGateway()->getSql()->select();
        $select->columns(array('nb' => new Expression('count(*)')))->where($where);
        $resultset = $this->getTableGateway()->selectWith($select);
        $row = $resultset->current();
        return $row->nb == 0;
    }
    
    /**
     * Vérifie si les colonnes date d'un millesime précisé ne sont pas nulles.
     * 
     * @param int $millesime
     * @return boolean
     */
    public function isValidMillesime($millesime)
    {
        return $this->isValidColDate($millesime, 'dateDebut') && $this->isValidColDate($millesime, 'dateFin') && $this->isValidColDate($millesime, 'echeance');
    }
    
    /**
     * Renvoie l'état du site vis à vis de la période d'inscripton.
     */
    public function etatDuSite()
    {
        $millesime = $this->getDefaultMillesime();
        $where = new Where();
        $where->expression('millesime = ?', $millesime)->literal('Nature="INS"');
        $resultset = $this->fetchAll($where);
        $row = $resultset->current();
        $dateDebut = DateTime::createFromFormat('Y-m-d', $row->dateDebut);
        $dateFin = DateTime::createFromFormat('Y-m-d', $row->dateFin);
        $aujourdhui = new DateTime();
        if ($aujourdhui < $dateDebut) {
            $msg = sprintf('La %s sera ouverte du %s au %s.', \mb_strtolower($row->description, 'utf-8'), $dateDebut->format('d/m/Y'), $dateFin->format('d/m/Y'));
            return array('etat' => 0, 'msg' => $msg);
        } elseif($aujourdhui > $dateFin) {
            $msg = sprintf('La %s est close.', \mb_strtolower($row->description, 'utf-8'));
            return array('etat' => 2, 'msg' => $msg);
        } else {
            $msg = sprintf('La %s est ouverte du %s au %s.', \mb_strtolower($row->description, 'utf-8'), $dateDebut->format('d/m/Y'), $dateFin->format('d/m/Y'));
            return array('etat' => 1, 'msg' => $msg);
        }
    }
}
 