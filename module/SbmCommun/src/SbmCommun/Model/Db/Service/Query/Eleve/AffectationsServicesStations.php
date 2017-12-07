<?php
/**
 * Requêtes donnant l'affectation ou la pré-affectation d'un élève
 *
 * 
 * @project sbm
 * @package package_name
 * @filesource AffectationsServicesStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 déc. 2017
 * @version 2017-2.3.14
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class AffectationsServicesStations implements FactoryInterface
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
     * @var int
     */
    protected $millesime;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     *
     * @var \Zend\Db\Sql\Select
     */
    protected $select;

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
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        $this->select = $this->sql->select()
            ->from(
            array(
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ))
            ->columns(
            array(
                'millesime' => 'millesime',
                'eleveId' => 'eleveId',
                'trajet' => 'trajet',
                'jours' => 'jours',
                'sens' => 'sens',
                'correspondance' => 'correspondance',
                'selection' => 'selection',
                'responsableId' => 'responsableId',
                'station1Id' => 'station1Id',
                'service1Id' => 'service1Id',
                'station2Id' => 'station2Id',
                'service2Id' => 'service2Id'
            ));
        return $this;
    }

    /**
     * Renvoie le ou les codes des services affectés à l'élève pour le domicile de ce responsable
     *
     * @param int $eleveId            
     * @param int $responsableId            
     * @param int $trajet
     *            1 ou 2 selon que c'est le responsable n°1 ou n°2
     *            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getServices($eleveId, $responsableId, $trajet = null)
    {
        $select = clone $this->select;
        $select->order(
            array(
                'trajet',
                'jours',
                'sens',
                'correspondance'
            ));
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('eleveId', $eleveId)
            ->equalTo('responsableId', $responsableId);
        if (isset($trajet)) {
            $where->equalTo('trajet', $trajet);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getAffectations($eleveId, $trajet = null, $annee_precedente = false)
    {
        $millesime = $this->millesime;
        if ($annee_precedente) {
            $millesime --;
        }
        $select = clone $this->select;
        $select->join(
            array(
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ), 'aff.service1Id = ser1.serviceId', 
            array(
                'service1' => 'nom',
                'operateur1' => 'operateur'
            ))
            ->join(
            array(
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ), 'ser1.transporteurId = tra1.transporteurId', 
            array(
                'transporteur1' => 'nom'
            ))
            ->join(
            array(
                'sta1' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'aff.station1Id = sta1.stationId', 
            array(
                'station1' => 'nom'
            ))
            ->join(
            array(
                'com1' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'sta1.communeId = com1.communeId', 
            array(
                'commune1' => 'nom'
            ))
            ->join(
            array(
                'sta2' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'aff.station2Id = sta2.stationId', 
            array(
                'station2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'com2' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'sta2.communeId = com2.communeId', 
            array(
                'commune2' => 'nom'
            ), $select::JOIN_LEFT)
            ->order(
            array(
                'trajet',
                'jours',
                'sens',
                'correspondance'
            ));
        $where = new Where();
        $where->equalTo('millesime', $millesime)->and->equalTo('eleveId', $eleveId);
        if (isset($trajet)) {
            $where->equalTo('trajet', $trajet);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getCorrespondances($eleveId)
    {
        $select = clone $this->select;
        $select->join(
            array(
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ), 'aff.service1Id = ser1.serviceId', 
            array(
                'service1' => 'nom',
                'operateur1' => 'operateur'
            ))
            ->join(
            array(
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ), 'ser1.transporteurId = tra1.transporteurId', 
            array(
                'transporteur1' => 'nom'
            ))
            ->join(
            array(
                'sta1' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'aff.station1Id = sta1.stationId', 
            array(
                'station1' => 'nom'
            ))
            ->join(
            array(
                'com1' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'sta1.communeId = com1.communeId', 
            array(
                'commune1' => 'nom'
            ))
            ->join(
            array(
                'cir1' => $this->db_manager->getCanonicName('circuits', 'table')
            ), 'ser1.serviceId = cir1.serviceId AND cir1.stationId = sta1.stationId', 
            array(
                'circuit1Id' => 'circuitId'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'ser2' => $this->db_manager->getCanonicName('services', 'table')
            ), 'aff.service2Id = ser2.serviceId', 
            array(
                'service2' => 'nom',
                'operateur2' => 'operateur'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'tra2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ), 'ser2.transporteurId = tra2.transporteurId', 
            array(
                'transporteur2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'sta2' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'aff.station2Id = sta2.stationId', 
            array(
                'station2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'com2' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'sta2.communeId = com2.communeId', 
            array(
                'commune2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'cir2' => $this->db_manager->getCanonicName('circuits', 'table')
            ), 'ser2.serviceId = cir2.serviceId AND cir2.stationId = sta2.stationId', 
            array(
                'circuit2Id' => 'circuitId'
            ), $select::JOIN_LEFT);
        $where = new Where();
        $where->equalTo('cir1.millesime', $this->millesime)->equalTo('aff.millesime', 
            $this->millesime)->and->equalTo('eleveId', $eleveId)
            ->nest()
            ->isNull('cir2.millesime')->or->equalTo('cir2.millesime', $this->millesime)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getLocalisation(Where $where, $order = null)
    {
        $select = $this->selectLocalisation($where, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function selectLocalisation(Where $where, $order = null)
    {
        $where->equalTo('aff.millesime', $this->millesime);
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = clone $this->select;
        ;
        $select->columns(
            array(
                'millesime' => 'millesime',
                'trajet' => 'trajet',
                'X' => new Expression('IF(sco.x = 0 AND sco.y = 0, res.x, sco.x)'),
                'Y' => new Expression('IF(sco.x = 0 AND sco.y = 0, res.y, sco.y)')
            ))
            ->join(
            array(
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ), 'ele.eleveId=aff.eleveId', 
            array(
                'id_ccda',
                'numero',
                'nom_eleve' => 'nomSA',
                'prenom_eleve' => 'prenomSA',
                'dateN'
            ))
            ->join(
            array(
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ), 'aff.millesime = sco.millesime AND aff.eleveId = sco.eleveId', 
            array(
                'transportGA' => new Expression(
                    'CASE WHEN demandeR2 > 0 THEN "Oui" ELSE "Non" END'),
                'x_eleve' => 'x',
                'y_eleve' => 'y',
                'chez',
                'adresseL1_chez' => 'adresseL1',
                'adresseL2_chez' => 'adresseL2',
                'codePostal_chez' => 'codePostal',
                'commentaire'
            ))
            ->join(
            array(
                'comsco' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'sco.communeId=comsco.communeId', 
            array(
                'commune_chez' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ), 'sco.etablissementId=eta.etablissementId', 
            array(
                'etablissement' => new Expression(
                    'CASE WHEN isnull(eta.alias) THEN eta.nom ELSE eta.alias END'),
                'x_etablissement' => 'x',
                'y_etablissement' => 'y'
            ))
            ->join(
            array(
                'cometa' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'cometa.communeId=eta.communeId', 
            array(
                'commune_etablissement' => 'nom'
            ))
            ->join(
            array(
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ), 'sco.classeId=cla.classeId', 
            array(
                'classe' => 'nom'
            ))
            ->join(
            array(
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ), 'res.responsableId=aff.responsableId', 
            array(
                'responsable' => new Expression('concat(res.nom," ",res.prenom)'),
                'x_responsable' => 'x',
                'y_responsable' => 'y',
                'telephoneF_responsable' => 'telephoneF',
                'telephoneP_responsable' => 'telephoneP',
                'telephoneT_responsable' => 'telephoneT',
                'email_responsable' => 'email',
                'adresseL1_responsable' => 'adresseL1',
                'adresseL2_responsable' => 'adresseL2',
                'codePostal_responsable' => 'codePostal'
            ))
            ->join(
            array(
                'comres' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'comres.communeId=res.communeId', 
            array(
                'commune_responsable' => 'nom'
            ))
            ->join(
            array(
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ), 'ser1.serviceId=aff.service1Id', 
            array(
                'service1' => 'serviceId'
            ))
            ->join(
            array(
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ), 'ser1.transporteurId=tra1.transporteurId', 
            array(
                'transporteur1' => 'nom'
            ))
            ->join(
            array(
                'sta1' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'aff.station1Id = sta1.stationId', 
            array(
                'station1' => 'nom'
            ))
            ->join(
            array(
                'com1' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'sta1.communeId = com1.communeId', 
            array(
                'commune1' => 'nom'
            ))
            ->join(
            array(
                'ser2' => $this->db_manager->getCanonicName('services', 'table')
            ), 'ser2.serviceId=aff.service2Id', 
            array(
                'service2' => 'serviceId'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'tra2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ), 'ser2.transporteurId=tra2.transporteurId', 
            array(
                'transporteur2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'sta2' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'aff.station2Id = sta2.stationId', 
            array(
                'station2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'com2' => $this->db_manager->getCanonicName('communes', 'table')
            ), 'sta2.communeId = com2.communeId', 
            array(
                'commune2' => 'nom'
            ), $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }

    public function paginatorScolaritesR($where, $order = null, $millesime = null)
    {
        $select = $this->selectScolaritesR($where, $order);
        // die($select->getSqlString());
        return new Paginator(new DbSelect($select, $this->db_manager->getDbAdapter()));
    }

    /**
     *
     * @param unknown $where            
     * @param string $order            
     * @param string $millesime            
     * @return \Zend\Db\Sql\Select
     */
    private function selectScolaritesR($where, $order = null, $millesime = null)
    {
        $select = clone $this->select;
        $select->join(
            array(
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ), 'ele.eleveId=aff.eleveId', 
            array(
                'numero',
                'nom',
                'nomSA',
                'prenom',
                'prenomSA',
                'dateN'
            ))
            ->join(
            array(
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ), 'res.responsableId=aff.responsableId', 
            array(
                'responsable' => new Expression('concat(res.nom," ",res.prenom)')
            ))
            ->join(
            array(
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ), 'sco.eleveId=aff.eleveId AND sco.millesime=aff.millesime', 
            array(
                'inscrit',
                'paiement',
                'fa'
            ))
            ->join(
            array(
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ), 'sco.etablissementId=eta.etablissementId', 
            array(
                'etablissement' => new Expression(
                    'CASE WHEN isnull(eta.alias) THEN eta.nom ELSE eta.alias END')
            ))
            ->join(
            array(
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ), 'sco.classeId=cla.classeId', 
            array(
                'classe' => 'nom'
            ))
            ->join(
            array(
                'sta1' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'sta1.stationId=aff.station1Id', 
            array(
                'station1' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'sta2' => $this->db_manager->getCanonicName('stations', 'table')
            ), 'sta2.stationId=aff.station2Id', 
            array(
                'station2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ), 'ser1.serviceId=aff.service1Id', 
            array(
                'service1' => 'nom'
            ))
            ->join(
            array(
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ), 'ser1.transporteurId=tra1.transporteurId', 
            array(
                'transporteur1' => 'nom'
            ))
            ->join(
            array(
                'ser2' => $this->db_manager->getCanonicName('services', 'table')
            ), 'ser2.serviceId=aff.service2Id', 
            array(
                'service2' => 'nom'
            ), $select::JOIN_LEFT)
            ->join(
            array(
                'tra2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ), 'ser2.transporteurId=tra2.transporteurId', 
            array(
                'transporteur2' => 'nom'
            ), $select::JOIN_LEFT);
        if (! empty($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }

    /**
     * Requête renvoyant téléphones portables pour les fiches filtrées par $where
     *
     * @param \Zend\Db\Sql\Where $where            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getTelephonesPortables(Where $where)
    {
        $select = $this->selectTelephonesPortables($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Paginator sur le même modèle que la requête précédente
     *
     * @param Where $where            
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorTelephonesPortables(Where $where)
    {
        $select = $this->selectTelephonesPortables($where);
        return new Paginator(new DbSelect($select, $this->db_manager->getDbAdapter()));
    }

    private function selectTelephonesPortables(Where $where)
    {
        $selectBase = clone $this->select;
        $selectBase->join(
            array(
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ), 'ser1.serviceId = aff.service1Id', array())
            ->join(
            array(
                'ser2' => $this->db_manager->getCanonicName('services', 'table')
            ), 'ser2.serviceId = aff.service2Id', array(), Select::JOIN_LEFT)
            ->join(
            array(
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ), 'aff.millesime = sco.millesime AND aff.eleveId = sco.eleveId', array())
            ->join(
            array(
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ), 'sco.etablissementId = eta.etablissementId', 
            array(
                'etablissement' => 'nom'
            ))
            ->join(
            array(
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ), 'aff.responsableId = res.responsableId', 
            array(
                'responsable' => new Expression('concat(res.nomSA, " ", res.prenomSA)'),
                'telephoneF',
                'telephoneP',
                'telephoneT'
            ))
            ->join(
            array( // utile uniquement pour filtrer sur nomSA, prenomSA ou numero
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ), 'ele.eleveId = aff.eleveId', 
            array(
                'eleve' => new Expression('concat(ele.nomSA, " ", ele.prenomSA)')
            ))
            ->where($where);
        // dans le champ des téléphones fixes
        $whereF = new Where();
        $whereF->like('telephoneF', '06%')->or->like('telephoneF', '07%');
        $selectF = $this->sql->select();
        $selectF->from(array(
            'telF' => $selectBase
        ))
            ->columns(
            array(
                'responsable',
                'telephone' => 'telephoneF',
                'eleve' => 'eleve',
                'service1' => 'service1Id',
                'service2' => 'service2Id',
                'etablissement' => 'etablissement'
            ))
            ->where($whereF);
        // dans le champ des téléphones portables
        $whereP = new Where();
        $whereP->like('telephoneP', '06%')->or->like('telephoneP', '07%');
        $selectP = $this->sql->select();
        $selectP->from(array(
            'telP' => $selectBase
        ))
            ->columns(
            array(
                'responsable',
                'telephone' => 'telephoneP',
                'eleve' => 'eleve',
                'service1' => 'service1Id',
                'service2' => 'service2Id',
                'etablissement' => 'etablissement'
            ))
            ->where($whereP);
        // dans le champ des téléphones du travail
        $whereT = new Where();
        $whereT->like('telephoneT', '06%')->or->like('telephoneT', '07%');
        $selectT = $this->sql->select();
        $selectT->from(array(
            'telT' => $selectBase
        ))
            ->columns(
            array(
                'responsable',
                'telephone' => 'telephoneT',
                'eleve' => 'eleve',
                'service1' => 'service1Id',
                'service2' => 'service2Id',
                'etablissement' => 'etablissement'
            ))
            ->where($whereT);
        
        $selectT->combine($selectP);
        
        $selectFPT = $this->sql->select();
        $selectFPT->from(array(
            'telPT' => $selectT
        ));
        $selectFPT->combine($selectF);
        $select = $this->sql->select();
        $select->from(array(
            'telFPT' => $selectFPT
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->order('responsable');
        
        // die(@$select->getSqlString());
        return $select;
    }
}