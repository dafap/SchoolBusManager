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
 * @date 26 mai 2015
 * @version 2015-1
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

class Eleves implements FactoryInterface
{

    protected $db;

    protected $sql;

    protected $select;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->sql = new Sql($this->db->getDbAdapter());
        $this->select = $this->sql->select()
            ->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
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
        ));
        return $this;
    }

    private function dernierMillesime($lequel, $responsableId)
    {
        $predicate = new Where();
        $predicate->literal('sc2.eleveId=sco.eleveId');
        $select2 = new Select();
        $select2->from(array(
            'sc2' => $this->db->getCanonicName('scolarites', 'table')
        ))
            ->columns(array(
            'dernierMillesime' => new Literal('max(millesime)')
        ))
            ->where($predicate);
        $where = new Where();
        $where->equalTo('res.responsableId', $responsableId)
            ->nest()
            ->isNull('millesime')->or->equalTo('millesime', $select2)->unnest();
        $select = clone $this->select;
        $select->join(array(
            'res' => $this->db->getCanonicName('responsables', 'table')
        ), 'res.responsableId = ele.' . $lequel, array())
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'ele.eleveId = sco.eleveId', array(
            'millesime', 'paiement', 'inscrit', 'fa', 'gratuit', 'demandeR1', 'demandeR2', 'accordR1', 'accordR2', 'subventionR1', 'subventionR2'
        ), $select::JOIN_LEFT)
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ), $select::JOIN_LEFT)
            ->join(array(
            'cometa' => $this->db->getCanonicName('communes', 'table')
        ), 'eta.communeId = cometa.communeId', array(
            'communeEtablissement' => 'nom'
        ), $select::JOIN_LEFT)
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 'cla.classeId = sco.classeId', array(
            'classe' => 'nom'
        ), $select::JOIN_LEFT)
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
 