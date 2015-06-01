<?php
/**
 * Gestion de la table système `calendar`
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
     * @return array Toutes les colonnes de la table sont renvoyées en filtrant les lignes dont la nature est 'AS'
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
        if (empty($millesime)) {
            $msg = 'Il faut au moins ouvrir une année scolaire.';
            die("<!DOCTYPE Html><html><head></head><body>$msg</body></html>");
        }
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
        $select = $this->getTableGateway()
            ->getSql()
            ->select();
        $select->columns(array(
            'millesime' => new Expression('max(millesime)')
        ));
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
        $select1 = $this->getTableGateway()
            ->getSql()
            ->select();
        $select1->columns(array(
            'millesime'
        ))->where($where1);
        
        $where = new Where();
        $where->literal('ouvert = 1')->notIn('millesime', $select1);
        $select = $this->getTableGateway()
            ->getSql()
            ->select()
            ->columns(array(
            'millesime' => new Expression('max(millesime)')
        ))
            ->where($where);
        $resultset = $this->getTableGateway()->selectWith($select);
        $row = $resultset->current();
        return $row->millesime;
    }

    /**
     * Vérifie si la colonne date précisée ne contient pas de valeur NULL pour le millesime indiqué.
     *
     * @param
     *            int millesime
     *            Millesime à vérifier
     * @param string $column
     *            Nom de la colonne
     *            
     * @return bool
     */
    private function isValidColDate($millesime, $column)
    {
        $where = new Where();
        $where->equalTo('millesime', $millesime)->isNull($column);
        $select = $this->getTableGateway()
            ->getSql()
            ->select();
        $select->columns(array(
            'nb' => new Expression('count(*)')
        ))->where($where);
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
     * Les dates sont données sous forme de DateTime.
     * L'état du site est :<ul>
     * <li>0 : inscriptions annoncées</li>
     * <li>1 : inscriptions ouvertes</li>
     * <li>2 : inscriptions fermées</li></ul>
     *
     * @return array Les clés du tableau sont :<ul>
     *         <li>'etat' (0, 1 ou 2)</li>
     *         <li>dateDebut (DateTime - début de la période d'inscription)</li>
     *         <li>dateFin (DateTime - fin de la période d'inscription)</li>
     *         <li>echeance (DateTime - date limite de paiement)</li></ul>
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
        $echeance = DateTime::createFromFormat('Y-m-d', $row->echeance);
        $aujourdhui = new DateTime();
        if ($aujourdhui < $dateDebut) {
            return array(
                'etat' => 0,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'echeance' => $echeance
            );
        } elseif ($aujourdhui > $dateFin) {
            return array(
                'etat' => 2,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'echeance' => $echeance
            );
        } else {
            return array(
                'etat' => 1,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'echeance' => $echeance
            );
        }
    }
    
    /**
     * Renvoie un tableau d'information sur les permanences
     *
     * @return array(string) - index à partir de 0
     */
    public function getPermanences($commune = null)
    {
        $millesime = $this->getDefaultMillesime();
        $where = new Where();
        $where->expression('millesime = ?', $millesime)->literal('Nature="PERM"');
        if (!empty($commune)) {
            $where->like('libelle', "%$commune%");
        }
        $resultset = $this->fetchAll($where, 'rang');
        $result = array();
        foreach ($resultset as $row) {
            $result[] = $row->description;
        }
        return $result;
    }

    /**
     * Change l'état d'une année scolaire.
     * Une année scolaire peut être ouverte (1) ou fermée (0).
     *
     * @param int $millesime
     *            millesime de l'année scolaire à traiter
     * @param int $ouvert
     *            Prend les valeurs 1 (ouvert) ou 0 (fermé)
     * @return \Zend\Db\TableGateway\int
     */
    public function changeEtat($millesime, $ouvert = 1)
    {
        return $this->table_gateway->update(array(
            'ouvert' => $ouvert
        ), array(
            'millesime' => $millesime
        ));
    }
}
 