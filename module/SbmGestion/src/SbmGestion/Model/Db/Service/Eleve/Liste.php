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
 * @date 11 jan. 2019
 * @version 2019-2.4.6
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Liste extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

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
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
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
    private function select($columns = [], $order = null, $distinct = true)
    {
        $select = $this->sql->select();
        $select->from(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'ele.eleveId=sco.eleveId', 
            empty($columns['sco']) ? [
                'inscrit',
                'paiement',
                'fa',
                'gratuit'
            ] : $columns['sco'])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', 
            empty($columns['eta']) ? [
                'etablissement' => 'nom'
            ] : $columns['eta'])
            ->join(
            [
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ], 'sco.classeId = cla.classeId', 
            empty($columns['cla']) ? [
                'classe' => 'nom'
            ] : $columns['cla'])
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 'aff.millesime=sco.millesime And sco.eleveId=aff.eleveId', 
            empty($columns['aff']) ? [
                'service1Id',
                'service2Id'
            ] : $columns['aff'])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'res.responsableId=aff.responsableId', 
            empty($columns['res']) ? [] : $columns['res'])
            ->join(
            [
                'comres' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'res.communeId=comres.communeId', 
            empty($columns['comres']) ? [] : $columns['comres'])
            ->join(
            [
                'comsco' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'comsco.communeId=sco.communeId', 
            empty($columns['comsco']) ? [] : $columns['comsco'], Select::JOIN_LEFT)
            ->join(
            [
                'sta1' => $this->db_manager->getCanonicName('stations', 'table')
            ], 'sta1.stationId=aff.station1Id', 
            empty($columns['sta1']) ? [] : $columns['sta1'], Select::JOIN_LEFT)
            ->join(
            [
                'sta2' => $this->db_manager->getCanonicName('stations', 'table')
            ], 'sta2.stationId=aff.station2Id', 
            empty($columns['sta2']) ? [] : $columns['sta2'], Select::JOIN_LEFT)
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId', 
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], $select::JOIN_LEFT);
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
        $columns = [
            'ele' => [
                'eleveId',
                'nom',
                'prenom',
                'adresseL1' => new Literal('IFNULL(sco.adresseL1, res.adresseL1)'),
                'adresseL2' => new Literal('IFNULL(sco.adresseL2, res.adresseL2)'),
                'codePostal' => new Literal('IFNULL(sco.codePostal, res.codePostal)'),
                'commune' => new Literal('IFNULL(comsco.nom, comres.nom)')
            ],
            'res' => [
                'email',
                'responsable' => new Literal(
                    'CONCAT(res.titre, " ", res.nom, " ", res.prenom)')
            ],
            'sta1' => [
                'station1' => 'nom'
            ],
            'sta2' => [
                'station2' => 'nom'
            ]
        ];
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
    public function query($millesime, $filtre, $order = ['commune', 'nom', 'prenom'])
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
    public function paginator($millesime, $filtre, $order = ['commune', 'nom', 'prenom'])
    {
        $select = $this->selectForGroup($millesime, $filtre, $order);
        return new Paginator(new DbSelect($select, $this->db_manager->getDbAdapter()));
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
    public function byEtablissementService($millesime, $filtre, $order = ['nom', 'prenom'])
    {
        $select = $this->selectByEtablissementService($millesime, $filtre, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByEtablissementService($millesime, $filtre, 
        $order = ['nom', 'prenom'])
    {
        $select = $this->selectByEtablissementService($millesime, $filtre, $order);
        return new Paginator(new DbSelect($select, $this->db_manager->getDbAdapter()));
    }

    private function selectByEtablissementService($millesime, $filtre, $order)
    {
        $tableAffectations = $this->db_manager->getCanonicName('affectations', 'table');
        $select1 = new Select();
        $select1->from([
            'a1' => $tableAffectations
        ])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'sens',
                'responsableId',
                'stationId' => 'station1Id',
                'serviceId' => 'service1Id'
            ])
            ->where([
            'a1.millesime' => $millesime
        ]);
        
        $select1cor2 = new Select();
        $select1cor2->from([
            'a1c2' => $tableAffectations
        ])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'sens',
                'responsableId',
                'stationId' => 'station1Id',
                'serviceId' => 'service1Id'
            ])
            ->where(
            [
                'a1c2.millesime' => $millesime,
                'correspondance' => 2
            ]);
        
        $jointure = "a2.millesime=correspondances.millesime AND a2.eleveId=correspondances.eleveId AND a2.trajet=correspondances.trajet AND a2.jours=correspondances.jours AND a2.sens=correspondances.sens AND a2.station2Id=correspondances.stationId";
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $millesime)
            ->isNotNull('service2Id')
            ->isNull('correspondances.millesime');
        $select2 = new Select();
        $select2->from([
            'a2' => $tableAffectations
        ])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'sens',
                'responsableId',
                'stationId' => 'station2Id',
                'serviceId' => 'service2Id'
            ])
            ->join([
            'correspondances' => $select1cor2
        ], $jointure, [], Select::JOIN_LEFT)
            ->where($where2);
        
        $where = new Where();
        $where->equalTo('s.millesime', $millesime);
        $select = $this->sql->select();
        $select->from(
            [
                'e' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join(
            [
                's' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'e.eleveId=s.eleveId', 
            [
                'inscrit',
                'paiement',
                'fa',
                'gratuit'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 's.etablissementId = eta.etablissementId', 
            [
                'etablissement' => 'nom'
            ])
            ->join(
            [
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ], 's.classeId = cla.classeId', 
            [
                'classe' => 'nom'
            ])
            ->join([
            'a' => $select1->combine($select2)
        ], 'a.millesime=s.millesime And e.eleveId=a.eleveId', 
            [
                'serviceId'
            ])
            ->join(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'r.responsableId=a.responsableId', 
            [
                'email',
                'responsable' => new Literal('CONCAT(r.titre, " ", r.nom, " ", r.prenom)')
            ])
            ->join(
            [
                'c' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId=c.communeId', [])
            ->join(
            [
                'd' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'd.communeId=s.communeId', [], Select::JOIN_LEFT)
            ->join(
            [
                'ser' => $this->db_manager->getCanonicName('services', 'table')
            ], 'ser.serviceId = a.serviceId', [])
            ->join(
            [
                'tra' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'tra.transporteurId = ser.transporteurId', 
            [
                'transporteur' => 'nom'
            ])
            ->join(
            [
                'sta' => $this->db_manager->getCanonicName('stations', 'table')
            ], 'sta.stationId = a.stationId', 
            [
                'station' => 'nom'
            ])
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = e.eleveId', 
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], $select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $filtre))
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'eleveId',
                'nom',
                'prenom',
                'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
                'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
                'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
                'commune' => new Literal('IFNULL(d.nom, c.nom)')
            ]);
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
    public function byTransporteur($millesime, $filtre, 
        $order = ['commune', 'nom', 'prenom'])
    {
        $select = $this->selectByTransporteur($millesime, $filtre, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByTransporteur($millesime, $filtre, 
        $order = ['commune', 'nom', 'prenom'])
    {
        $select = $this->selectByTransporteur($millesime, $filtre, $order);
        return new Paginator(new DbSelect($select, $this->db_manager->getDbAdapter()));
    }

    private function selectByTransporteur($millesime, $filtre, $order)
    {
        $tableAffectations = $this->db_manager->getCanonicName('affectations', 'table');
        $select1 = new Select();
        $select1->from([
            'a1' => $tableAffectations
        ])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'sens',
                'responsableId',
                'stationId' => 'station1Id',
                'serviceId' => 'service1Id'
            ])
            ->where([
            'a1.millesime' => $millesime
        ]);
        
        $select1cor2 = new Select();
        $select1cor2->from([
            'a1c2' => $tableAffectations
        ])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'sens',
                'responsableId',
                'stationId' => 'station1Id',
                'serviceId' => 'service1Id'
            ])
            ->where(
            [
                'a1c2.millesime' => $millesime,
                'correspondance' => 2
            ]);
        
        $jointure = "a2.millesime=correspondances.millesime AND a2.eleveId=correspondances.eleveId AND a2.trajet=correspondances.trajet AND a2.jours=correspondances.jours AND a2.sens=correspondances.sens AND a2.station2Id=correspondances.stationId";
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $millesime)
            ->isNotNull('service2Id')
            ->isNull('correspondances.millesime');
        $select2 = new Select();
        $select2->from([
            'a2' => $tableAffectations
        ])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'sens',
                'responsableId',
                'stationId' => 'station2Id',
                'serviceId' => 'service2Id'
            ])
            ->join([
            'correspondances' => $select1cor2
        ], $jointure, [], Select::JOIN_LEFT)
            ->where($where2);
        
        $where = new Where();
        $where->equalTo('s.millesime', $millesime);
        $select = $this->sql->select();
        $select->from(
            [
                'e' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join(
            [
                's' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'e.eleveId=s.eleveId', 
            [
                'inscrit',
                'paiement',
                'fa',
                'gratuit'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 's.etablissementId = eta.etablissementId', 
            [
                'etablissement' => 'nom'
            ])
            ->join(
            [
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ], 's.classeId = cla.classeId', 
            [
                'classe' => 'nom'
            ])
            ->join([
            'a' => $select1->combine($select2)
        ], 'a.millesime=s.millesime And e.eleveId=a.eleveId', [])
            ->join(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'r.responsableId=a.responsableId', 
            [
                'email',
                'responsable' => new Literal('CONCAT(r.titre, " ", r.nom, " ", r.prenom)')
            ])
            ->join(
            [
                'c' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId=c.communeId', [])
            ->join(
            [
                'd' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'd.communeId=s.communeId', [], Select::JOIN_LEFT)
            ->join(
            [
                'ser' => $this->db_manager->getCanonicName('services', 'table')
            ], 'ser.serviceId = a.serviceId', 
            [
                'serviceId'
            ])
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = e.eleveId', 
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], $select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $filtre))
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'eleveId',
                'nom',
                'prenom',
                'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
                'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
                'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
                'commune' => new Literal('IFNULL(d.nom, c.nom)')
            ]);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select            
     *
     * @return string
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }
}