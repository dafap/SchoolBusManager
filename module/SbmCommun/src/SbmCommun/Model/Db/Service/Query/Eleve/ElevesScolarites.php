<?php
/**
 * Requêtes permettant d'obtenir les enfants inscrits ou préinscrits d'un responsable donné
 * (enregistré dans module.config.php sous l'alias 'Sbm\Db\Query\ElevesScolarites')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource ElevesScolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Console\Prompt\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class ElevesScolarites implements FactoryInterface
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
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'ele.eleveId = sco.eleveId', array(
            'millesime' => 'millesime',
            'selectionScolarite' => 'selection',
            'dateInscription' => 'dateInscription',
            'dateModificationScolarite' => 'dateModification',
            'etablissementId' => 'etablissementId',
            'classeId' => 'classeId',
            'chez' => 'chez',
            'adresseEleveL1' => 'adresseL1',
            'adresseEleveL2' => 'adresseL2',
            'codePostalEleve' => 'codePostal',
            'communeEleveId' => 'communeId',
            'x' => 'x',
            'y' => 'y',
            'distanceR1' => 'distanceR1',
            'distanceR2' => 'distanceR2',
            'dateEtiquette' => 'dateEtiquette',
            'dateCarte' => 'dateCarte',
            'inscrit' => 'inscrit',
            'gratuit' => 'gratuit',
            'paiement' => 'paiement',
            'fa' => 'fa',
            'anneeComplete' => 'anneeComplete',
            'subventionR1' => 'subventionR1',
            'subventionR2' => 'subventionR2',
            'demandeR1' => 'demandeR1',
            'demandeR2' => 'demandeR2',
            'accordR1' => 'accordR1',
            'accordR2' => 'accordR2',
            'internet' => 'internet',
            'district' => 'district',
            'derogation' => 'derogation',
            'dateDebut' => 'dateDebut',
            'dateFin' => 'dateFin',
            'joursTransport' => 'joursTransport',
            'subventionTaux' => 'subventionTaux',
            'tarifId' => 'tarifId',
            'regimeId' => 'regimeId',
            'motifDerogation' => 'motifDerogation',
            'motifRefusR1' => 'motifRefusR1',
            'motifRefusR2' => 'motifRefusR2',
            'commentaire' => 'commentaire'
        ))
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom',
            'xeta' => 'x',
            'yeta' => 'y'
        ))
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'eta.communeId = com.communeId', array(
            'communeEtablissement' => 'nom'
        ));
        return $this;
    }

    public function getEleve($eleveId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->equalTo('ele.eleveId', $eleveId);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute()->current();
    }

    public function getEleveAdresse($eleveId, $trajet)
    {
        $select = clone $this->select;
        $select->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), $trajet == 1 ? 'ele.responsable1Id = r.responsableId' : 'ele.responsable2Id=r.responsableId', array(
            'responsableId' => 'responsableId',
            'adresseL1' => 'adresseL1',
            'adresseL2' => 'adresseL2',
            'codePostal' => 'codePostal',
            'x' => 'x',
            'y' => 'y'
        ))
            ->join(array(
            'comresp' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId = comresp.communeId', array(
            'commune' => 'nom'
        ));
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->equalTo('ele.eleveId', $eleveId);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getElevesInscrits($responsableId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->and->nest()->literal('paiement = 1')->or->literal('fa=1')->unnest()->and->nest()->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getElevesPreinscrits($responsableId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->literal('paiement = 0')
            ->literal('fa=0')->and->nest()->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }
    
    public function getElevesPreinscritsWithMontant($responsableId)
    {
        $select = clone $this->select;
        $select->join(array('tar' => $this->db->getCanonicName('tarifs', 'table')), 'tar.tarifId = sco.tarifId', array('montant', 'nomTarif' => 'nom'));
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
        ->literal('paiement = 0')
        ->literal('fa=0')->and->nest()->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getInscritsNonAffectes()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->sqlInscritsNonAffectes());
        return $statement->execute();
    }
    public function paginatorInscritsNonAffectes()
    {
        return new Paginator(new DbSelect($this->sqlInscritsNonAffectes(), $this->db->getDbAdapter()));
    }
    private function sqlInscritsNonAffectes()
    {
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId', array(
            'estR1' => new Expression('CASE WHEN r.responsableId=ele.responsable1Id THEN 1 ELSE 0 END'),
            'responsableId' => 'responsableId',
            'adresseL1' => 'adresseL1',
            'adresseL2' => 'adresseL2'
        ))
            ->join(array(
            'comresp' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId = comresp.communeId', array(
            'commune' => 'nom'
        ))
            ->join(array(
            'aff' => $this->db->getCanonicName('affectations', 'table')
        ), 'aff.eleveId=ele.eleveId AND aff.responsableId=r.responsableId AND aff.millesime=sco.millesime', array(), $select::JOIN_LEFT);
        // inscrit : bon millesime dans scolarites et paiement enregistré ou fa
        $predicate1 = new Predicate();
        $predicate1->equalTo('sco.millesime', Session::get('millesime'))
            ->nest()
            ->literal('paiement = 1')->or->literal('fa=1')->unnest();
        // demande non traitée
        $predicate2 = new Predicate();
        $predicate2->nest()->literal('ele.responsable1Id = r.responsableId')->and->literal('sco.demandeR1 = 1')->unnest()->OR->nest()->literal('ele.responsable2Id=r.responsableId')->and->literal('sco.demandeR2 = 1')->unnest();
        // accord mais pas d'affectation
        $predicate3 = new Predicate();
        $predicate3->isNull('aff.eleveId')
            ->nest()
            ->nest()
            ->literal('ele.responsable1Id = r.responsableId')->and->literal('sco.demandeR1 = 2')
            ->literal('sco.accordR1 = 1')
            ->unnest()->or->nest()->literal('ele.responsable1Id = r.responsableId')->and->literal('sco.demandeR1 = 2')
            ->literal('sco.accordR2 = 1')
            ->unnest()
            ->unnest();
        // composition du where
        $where = new Where();
        $where->predicate($predicate1)
            ->nest()
            ->predicate($predicate2)->or->predicate($predicate3)->unnest();
        return $select->where($where)->order(array('nom', 'prenom'));
    }

    public function getPreinscritsNonAffectes()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->sqlPreinscritsNonAffectes());
        return $statement->execute();
    }
    public function paginatorPreinscritsNonAffectes()
    {
        return new Paginator(new DbSelect($this->sqlPreinscritsNonAffectes(), $this->db->getDbAdapter()));
    }
    private function sqlPreinscritsNonAffectes()
    {
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId', array(
            'estR1' => new Expression('CASE WHEN r.responsableId=ele.responsable1Id THEN 1 ELSE 0 END'),
            'responsableId' => 'responsableId',
            'adresseL1' => 'adresseL1',
            'adresseL2' => 'adresseL2'
        ))
            ->join(array(
            'comresp' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId = comresp.communeId', array(
            'commune' => 'nom'
        ))
            ->join(array(
            'aff' => $this->db->getCanonicName('affectations', 'table')
        ), 'aff.eleveId=ele.eleveId AND aff.responsableId=r.responsableId AND aff.millesime=sco.millesime', array(), $select::JOIN_LEFT);
        // inscrit : bon millesime dans scolarites et paiement enregistré ou fa
        $predicate1 = new Predicate();
        $predicate1->equalTo('sco.millesime', Session::get('millesime'))
            ->literal('paiement = 0')
            ->literal('fa=0');
        // demande non traitée
        $predicate2 = new Predicate();
        $predicate2->nest()
            ->literal('ele.responsable1Id = r.responsableId')
            ->literal('sco.demandeR1 = 1')
            ->unnest()->OR->nest()
            ->literal('ele.responsable2Id=r.responsableId')
            ->literal('sco.demandeR2 = 1')
            ->unnest();
        // accord mais pas d'affectation
        $predicate3 = new Predicate();
        $predicate3->isNull('aff.eleveId')
            ->nest()
            ->nest()
            ->literal('ele.responsable1Id = r.responsableId')->and->literal('sco.demandeR1 = 2')
            ->literal('sco.accordR1 = 1')
            ->unnest()->or->nest()->literal('ele.responsable1Id = r.responsableId')->and->literal('sco.demandeR1 = 2')
            ->literal('sco.accordR2 = 1')
            ->unnest()
            ->unnest();
        // composition du where
        $where = new Where();
        $where->predicate($predicate1)
            ->nest()
            ->predicate($predicate2)->or->predicate($predicate3)->unnest();
        return $select->where($where)->order(array('nom', 'prenom'));
    }

    public function getDemandeGaDistanceR2Zero()
    {
        $select = clone $this->select;
        $select->join(array(
            'r1' => $this->db->getCanonicName('responsables', 'table')
        ), 'ele.responsable1Id = r1.responsableId', array(
            'adresseR1L1' => 'adresseL1',
            'adresseR1L2' => 'adresseL2'
        ))
            ->join(array(
            'comr1' => $this->db->getCanonicName('communes', 'table')
        ), 'r1.communeId = comr1.communeId', array(
            'communeR1' => 'nom'
        ))
            ->join(array(
            'r2' => $this->db->getCanonicName('responsables', 'table')
        ), 'ele.responsable2Id = r2.responsableId', array(
            'adresseR2L1' => 'adresseL1',
            'adresseR2L2' => 'adresseL2'
        ), $select::JOIN_LEFT)
            ->join(array(
            'comr2' => $this->db->getCanonicName('communes', 'table')
        ), 'r2.communeId = comr2.communeId', array(
            'communeR2' => 'nom'
        ), $select::JOIN_LEFT);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->isNotNull('responsable2Id')
            ->literal('demandeR2 = 1')
            ->literal('distanceR2 = 0');
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getEnfants($responsableId, $ga = 1)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->equalTo(sprintf('responsable%dId', $ga), $responsableId);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }
}