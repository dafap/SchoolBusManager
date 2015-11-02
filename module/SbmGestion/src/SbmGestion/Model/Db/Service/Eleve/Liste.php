<?php
/**
 * Listes d'élèves affectés pour un ou plusieurs critères donnés
 * 
 * La jointure sur la table affectations nécessite que l'affectation soit faite (voir méthode select()).
 * 
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 octobre 2015
 * @version 2015-1.6.5
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class Liste extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     * La nouvelle version n'initialise plus la propriété $select
     *
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $this->db->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    /**
     * Construit un select standard et précise les colonnes des tables.
     * Les colonnes sont présentées dans un tableau associatif où la clé est
     * l'alias de la table concernée. Les expressions portant sur des champs
     * de plusieurs tables doivent être associées à l'alias `ele`
     *
     * @param array $columns
     *            tableau associatif pécisant les colonnes à obtenir
     *            Les clés sont :<ul>
     *            <li>`ele` pour la table eleves et les expressions portant des champs préfixés</li>
     *            <li>`sco` pour la table scolarites</li>
     *            <li>`eta` pour la table etablissements</li>
     *            <li>`cla` pour la table classes</li>
     *            <li>`aff` pour la table affectations</li>
     *            <li>`res` pour la table responsables</li>
     *            <li>`comres` pour la table communes du responsable</li>
     *            <li>`comsco` pour la table communes de l'adresse perso d'un élève</li></ul>
     *            A chaque clé est associé le tableau des colonnes à obtenir dans cette table.
     * @param array $order
     *            tableau de colonnes
     * @param bool $distinct            
     *
     * @return \Zend\Db\Sql\Select
     */
    private function select($columns = array(), $order = null, $distinct = true)
    {
        $select = $this->sql->select();
        $select->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'ele.eleveId=sco.eleveId', empty($columns['sco']) ? array(
            'inscrit',
            'paiement',
            'fa',
            'gratuit'
        ) : $columns['sco'])
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', empty($columns['eta']) ? array(
            'etablissement' => 'nom'
        ) : $columns['eta'])
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 'sco.classeId = cla.classeId', empty($columns['cla']) ? array(
            'classe' => 'nom'
        ) : $columns['cla'])
            ->join(array(
            'aff' => $this->db->getCanonicName('affectations', 'table')
        ), 'aff.millesime=sco.millesime And sco.eleveId=aff.eleveId', empty($columns['aff']) ? array() : $columns['aff'])
            ->join(array(
            'res' => $this->db->getCanonicName('responsables', 'table')
        ), 'res.responsableId=aff.responsableId', empty($columns['res']) ? array() : $columns['res'])
            ->join(array(
            'comres' => $this->db->getCanonicName('communes', 'table')
        ), 'res.communeId=comres.communeId', empty($columns['comres']) ? array() : $columns['comres'])
            ->join(array(
            'comsco' => $this->db->getCanonicName('communes', 'table')
        ), 'comsco.communeId=sco.communeId', empty($columns['comsco']) ? array() : $columns['comsco'], Select::JOIN_LEFT);
        if (! empty($columns['ele'])) {
            $select->columns($columns['ele']);
        }
        if (! empty($order)) {
            $select->order($order);
        }
        if ($distinct) {
            $select->quantifier(Select::QUANTIFIER_DISTINCT);
        }
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoi un Select avec les colonnes qui vont bien pour les groupes d'élèves.
     * Le sélect est filtré par le filtre donné.
     * Utilisé dans les requêtes (by...) et les paginator...
     *
     * @param int $millesime            
     * @param array $filtre            
     * @param array $order            
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectForGroup($millesime, $filtre, $order)
    {
        $columns = array(
            'ele' => array(
                'eleveId',
                'nom',
                'prenom',
                'adresseL1' => new Literal('IFNULL(sco.adresseL1, res.adresseL1)'),
                'adresseL2' => new Literal('IFNULL(sco.adresseL2, res.adresseL2)'),
                'codePostal' => new Literal('IFNULL(sco.codePostal, res.codePostal)'),
                'commune' => new Literal('IFNULL(comsco.nom, comres.nom)')
            ),
            'res' => array(
                'email',
                'responsable' => new Literal('CONCAT(res.titre, " ", res.nom, " ", res.prenom)')
            )
        );
        $select = $this->select($columns, $order);
        $where = new Where();
        $where->equalTo('sco.millesime', $millesime);
        $select->where($this->arrayToWhere($where, $filtre));
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un filtre donnés et dans l'ordre demandé
     *
     * @param int $millesime            
     * @param array $filtre            
     * @param array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function query($millesime, $filtre, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectForGroup($millesime, $filtre, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Renvoie la même liste paginée
     *
     * @param int $millesime            
     * @param array $filtre            
     * @param array $order            
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginator($millesime, $filtre, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectForGroup($millesime, $filtre, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    /**
     * Renvoie la liste des élèves pour un millesime, un établissement et un service donnés
     * Traitement spécial pour
     *
     * @param int $millesime            
     * @param int $etablissementId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byEtablissementService($millesime, $etablissementId, $serviceId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByEtablissementService($millesime, $etablissementId, $serviceId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByEtablissementService($millesime, $etablissementId, $serviceId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByEtablissementService($millesime, $etablissementId, $serviceId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByEtablissementService($millesime, $etablissementId, $serviceId, $order)
    {
        $tableAffectations = $this->db->getCanonicName('affectations', 'table');
        $select1 = new Select();
        $select1->from(array(
            'a1' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
            ->where(array(
            'a1.millesime' => $millesime
        ));
        
        $select1cor2 = new Select();
        $select1cor2->from(array(
            'a1c2' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
            ->where(array(
            'a1c2.millesime' => $millesime,
            'correspondance' => 2
        ));
        
        $jointure = "a2.millesime=correspondances.millesime AND a2.eleveId=correspondances.eleveId AND a2.trajet=correspondances.trajet AND a2.jours=correspondances.jours AND a2.sens=correspondances.sens AND a2.station2Id=correspondances.stationId";
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $millesime)
            ->isNotNull('service2Id')
            ->isNull('correspondances.millesime');
        $select2 = new Select();
        $select2->from(array(
            'a2' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station2Id',
            'serviceId' => 'service2Id'
        ))
            ->join(array(
            'correspondances' => $select1cor2
        ), $jointure, array(), Select::JOIN_LEFT)
            ->where($where2);
        
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)
            ->literal('inscrit = 1')
            ->equalTo('s.etablissementId', $etablissementId)
            ->equalTo('a.serviceId', $serviceId);
        $select = $this->sql->select();
        $select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array(
            'inscrit',
            'paiement',
            'fa',
            'gratuit'
        ))
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 's.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 's.classeId = cla.classeId', array(
            'classe' => 'nom'
        ))
            ->join(array(
            'a' => $select1->combine($select2)
        ), 'a.millesime=s.millesime And e.eleveId=a.eleveId', array())
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'r.responsableId=a.responsableId', array(
            'email',
            'responsable' => new Literal('CONCAT(r.titre, " ", r.nom, " ", r.prenom)')
        ))
            ->join(array(
            'c' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId=c.communeId', array())
            ->join(array(
            'd' => $this->db->getCanonicName('communes', 'table')
        ), 'd.communeId=s.communeId', array(), Select::JOIN_LEFT)
            ->join(array(
            'sta' => $this->db->getCanonicName('stations', 'table')
        ), 'sta.stationId = a.stationId', array(
            'station' => 'nom'
        ))
            ->where($where)
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array(
            'eleveId',
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un transporteur donnés
     *
     * @param int $millesime            
     * @param int $transporteurId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byTransporteur($millesime, $filtre, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByTransporteur($millesime, $filtre, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByTransporteur($millesime, $filtre, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByTransporteur($millesime, $filtre, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByTransporteur($millesime, $filtre, $order)
    {
        $tableAffectations = $this->db->getCanonicName('affectations', 'table');
        $select1 = new Select();
        $select1->from(array(
            'a1' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
            ->where(array(
            'a1.millesime' => $millesime
        ));
        
        $select1cor2 = new Select();
        $select1cor2->from(array(
            'a1c2' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
            ->where(array(
            'a1c2.millesime' => $millesime,
            'correspondance' => 2
        ));
        
        $jointure = "a2.millesime=correspondances.millesime AND a2.eleveId=correspondances.eleveId AND a2.trajet=correspondances.trajet AND a2.jours=correspondances.jours AND a2.sens=correspondances.sens AND a2.station2Id=correspondances.stationId";
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $millesime)
            ->isNotNull('service2Id')
            ->isNull('correspondances.millesime');
        $select2 = new Select();
        $select2->from(array(
            'a2' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station2Id',
            'serviceId' => 'service2Id'
        ))
            ->join(array(
            'correspondances' => $select1cor2
        ), $jointure, array(), Select::JOIN_LEFT)
            ->where($where2);
        
        $where = new Where();
        $where->equalTo('s.millesime', $millesime);
        $select = $this->sql->select();
        $select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array(
            'inscrit',
            'paiement',
            'fa',
            'gratuit'
        ))
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 's.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 's.classeId = cla.classeId', array(
            'classe' => 'nom'
        ))
            ->join(array(
            'a' => $select1->combine($select2)
        ), 'a.millesime=s.millesime And e.eleveId=a.eleveId', array())
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'r.responsableId=a.responsableId', array(
            'email',
            'responsable' => new Literal('CONCAT(r.titre, " ", r.nom, " ", r.prenom)')
        ))
            ->join(array(
            'c' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId=c.communeId', array())
            ->join(array(
            'd' => $this->db->getCanonicName('communes', 'table')
        ), 'd.communeId=s.communeId', array(), Select::JOIN_LEFT)
            ->join(array(
            'ser' => $this->db->getCanonicName('services', 'table')
        ), 'ser.serviceId = a.serviceId', array(
            'serviceId'
        ))
            ->where($this->arrayToWhere($where, $filtre))
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array(
            'eleveId',
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        // die($this->getSqlString($select));
        return $select;
    }

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
}