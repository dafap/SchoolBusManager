<?php
/**
 * Requête permettant d'obtenir des détails sur les élèves
 *
 * La table principale est `eleves`. Les tables jointes le sont par des LEFT JOIN ce qui rend les jointures non exclusives.
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Predicate\Predicate;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Eleves implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select            
     *
     * @return \Zend\Db\Adapter\mixed
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    private function dernierMillesime($lequel, $responsableId)
    {
        $predicate = new Where();
        $predicate->literal('sc2.eleveId=sco.eleveId');
        $select2 = new Select();
        $select2->from(array(
            'sc2' => $this->db_manager->getCanonicName('scolarites', 'table')
        ))
            ->columns(array(
            'dernierMillesime' => new Literal('max(millesime)')
        ))
            ->where($predicate);
        $where = new Where();
        $where->equalTo('res.responsableId', $responsableId)
            ->nest()
            ->isNull('millesime')->or->equalTo('millesime', $select2)->unnest();
        $select = $this->sql->select()
            ->from(array(
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ))
            ->columns(array(
            'eleveId' => 'eleveId',
            'dateCreation' => 'dateCreation',
            'dateModificationEleve' => 'dateModification',
            'nom' => 'nom',
            'nomSA' => 'nomSA',
            'prenom' => 'prenom',
            'prenomSA' => 'prenomSA',
            'dateN' => 'dateN',
            'numero' => 'numero',
            'responsable1Id' => 'responsable1Id',
            'x1' => 'x1',
            'y1' => 'y1',
            'responsable2Id' => 'responsable2Id',
            'x2' => 'x2',
            'y2' => 'y2',
            'responsableFId' => 'responsableFId',
            'selectionEleve' => 'selection',
            'noteEleve' => 'note'
        ))
            ->join(array(
            'res' => $this->db_manager->getCanonicName('responsables', 'table')
        ), 'res.responsableId = ele.' . $lequel, array())
            ->join(array(
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ), 'ele.eleveId = sco.eleveId', array(
            'millesime',
            'paiement',
            'inscrit',
            'fa',
            'gratuit',
            'demandeR1',
            'demandeR2',
            'accordR1',
            'accordR2',
            'subventionR1',
            'subventionR2'
        ), Select::JOIN_LEFT)
            ->join(array(
            'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ), Select::JOIN_LEFT)
            ->join(array(
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'eta.communeId = cometa.communeId', array(
            'communeEtablissement' => 'nom'
        ), Select::JOIN_LEFT)
            ->join(array(
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ), 'cla.classeId = sco.classeId', array(
            'classe' => 'nom'
        ), Select::JOIN_LEFT)
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function duResponsable1($responsableId)
    {
        return $this->dernierMillesime('responsable1Id', $responsableId);
    }

    public function duResponsable2($responsableId)
    {
        return $this->dernierMillesime('responsable2Id', $responsableId);
    }

    public function duResponsableFinancier($responsableId)
    {
        return $this->dernierMillesime('responsableFId', $responsableId);
    }
}
 