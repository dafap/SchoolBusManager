<?php
/**
 * Renvoie un tableau d'effectifs 
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource Effectif.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 octobre 2015
 * @version 2015-2
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use DafapSession\Model\Session;
use Zend\Db\Sql\Having;

class Effectif extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var integer
     */
    private $millesime;

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
     *
     * @var array
     */
    private $tableName = array();

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->millesime = Session::get('millesime');
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $db->getDbAdapter();
        $this->tableName['affectations'] = $db->getCanonicName('affectations', 'table');
        $this->tableName['circuits'] = $db->getCanonicName('circuits', 'table');
        $this->tableName['communes'] = $db->getCanonicName('communes', 'table');
        $this->tableName['eleves'] = $db->getCanonicName('eleves', 'table');
        $this->tableName['responsables'] = $db->getCanonicName('responsables', 'table');
        $this->tableName['scolarites'] = $db->getCanonicName('scolarites', 'table');
        $this->tableName['services'] = $db->getCanonicName('services', 'table');
        $this->sql = new Sql($db->getDbAdapter());
        return $this;
    }

    /**
     * Renvoie les effectifs des circuits, station par station, dans un tableau
     *
     * @param bool $sanspreinscrits            
     * @return array
     */
    public function byCircuit($sanspreinscrits = false)
    {
        // SELECT circuitId, count(*) FROM `sbm_t_circuits` c JOIN `sbm_t_affectations` a ON c.serviceId=a.service1Id AND c.stationId=a.station1Id WHERE millesime=2014 GROUP BY circuitId
        $result = array();
        if ($sanspreinscrits) {
            $filtre = array(
                'inscrit' => 1,
                array(
                    'paiement' => 1,
                    'or',
                    'fa' => 1,
                    'or',
                    '>' => array(
                        'gratuit',
                        0
                    )
                )
            );
        } else {
            $filtre = array(
                'inscrit' => 1
            );
        }
        $rowset = $this->requeteCir(1, 'circuitId', $filtre, 'circuitId');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteCir(2, 'circuitId', array(), 'circuitId');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    /**
     * Compte les élèves dans les communes des responsables (1, 2) et de résidence (3)
     * Le résultat est un tableau associatif où les clés sont des communeId et les valeurs
     * sont des structures de la forme
     * array(
     * 'demandes' => array('r1' => effectif1, 'r2' => effectif2, 'ele' => effectif3),
     * 'transportes' => array('r1' => effectif4, 'r2' => effectif5, 'ele' => effectif6),
     * 'total' => array('demandes' => effectif_des_demandes, 'transportes' => effectif_des_transportes)
     * ) où r1 correspond à la commune du responsable1 lorsque l'élève n'a pas d'adresse perso,
     * r2 à la commune du responsable2
     * ele à la commune de l'élève s'il a une adresse perso
     * Les effectifs sont dans total en distinguant les demandes et les transportés (affectation)
     *
     * @return array
     */
    public function byCommune()
    {
        $result = array();
        // calcul du nombre d'élèves inscrits ou préinscrits ( = sauf rayés)
        // - pour la commune du R1 : inscrit = 1 AND sco.communeId IS NULL AND demandeR1 > 0
        $filtre = array(
            'inscrit' => 1,
            '>' => array(
                'demandeR1',
                0
            ),
            'is null' => array(
                's.communeId'
            )
        );
        $rowset = $this->requeteCom(1, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['demandes']['r1'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, array(
            'a.trajet' => 1,
            'a.correspondance' => 1
        ));
        $rowset = $this->requeteCom(4, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['transportes']['r1'] = $row['effectif'];
        }
        
        // - pour la commune du R2 : inscrit = 1 AND demandeR2 > 0
        $filtre = array(
            'inscrit' => 1,
            '>' => array(
                'demandeR2',
                0
            )
        );
        $rowset = $this->requeteCom(2, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['demandes']['r2'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, array(
            'a.trajet' => 2,
            'a.correspondance' => 1
        ));
        $rowset = $this->requeteCom(5, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['transportes']['r2'] = $row['effectif'];
        }
        
        // - pour la commune de l'élève lorsqu'il a une adresse personnelle :
        // inscrit = 1 AND sco.communeId IS NOT NULL AND demandeR1 > 0
        $filtre = array(
            'inscrit' => 1,
            '>' => array(
                'demandeR1',
                0
            ),
            'is not null' => array(
                's.communeId'
            )
        );
        $rowset = $this->requeteCom(3, 'communeId', $filtre, 'communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['demandes']['ele'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, array(
            'a.trajet' => 1,
            'a.correspondance' => 1
        ));
        $rowset = $this->requeteCom(6, 'communeId', $filtre, 'communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['transportes']['ele'] = $row['effectif'];
        }
        
        // calcul du nombre d'élèves
        foreach ($result as $key => &$value) {
            $value['total']['demandes'] = array_sum($value['demandes']);
            $value['total']['transportes'] = array_sum($value['transportes']);
        }
        return $result;
    }

    /**
     * Renvoie un tableau d'effectifs des demandes et des élèves transportés par classe
     *
     * @return array
     */
    public function byClasse()
    {
        // SELECT classeId, count(*) FROM sbm_t_scolarites WHERE inscrit = 1 GROUP BY classeId
        $result = array();
        $filtre = array(
            'inscrit' => 1
        );
        $group = array(
            'classeId'
        );
        $rowset = $this->requeteCl('classeId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['classeId']]['demandes'] = $row['effectif'];
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('classeId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['classeId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    /**
     * Tableau d'effectifs par établissement des demandes et des élèves transportés
     *
     * @return array
     */
    public function byEtablissement()
    {
        // SELECT etablissementId, count(*) FROM sbm_scolarites GROUP BY etablissementId
        $result = array();
        $group = array(
            'etablissementId'
        );
        $filtre = array(
            'inscrit' => 1
        );
        $rowset = $this->requeteCl('etablissementId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['demandes'] = $row['effectif'];
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('etablissementId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    public function byEtablissementGivenService($serviceId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            's.inscrit' => 1,
            'service1Id' => $serviceId
        ), 'etablissementId');
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            's.inscrit' => 1,
            'a.service2Id' => $serviceId
        ), 'etablissementId');
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['r2'] = $row['effectif'];
        }
        
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byOrganisme()
    {
        // SELECT organismeId, count(*) FROM `sbm_t_scolarites` s WHERE millesime=2014 GROUP BY organismeId
        $result = array();
        $group = array('organismeId');
        $filtre = array('s.inscrit' => 1);
        $rowset = $this->requeteCl('organismeId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['organismeId']]['demandes'] = $row['effectif'];
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('organismeId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['organismeId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    public function byServiceGivenEtablissement($etablissementId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            's.inscrit' => 1,
            'etablissementId' => $etablissementId
        ), 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $result[$row['service1Id']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            's.inscrit' => 1,
            'etablissementId' => $etablissementId
        ), 'service2Id');
        foreach ($rowset as $row) {
            if ($row['service2Id'] == '')
                continue;
            $result[$row['service2Id']]['r2'] = $row['effectif'];
        }
        
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byServiceGivenStation($stationId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            's.inscrit' => 1,
            'station1Id' => $stationId
        ), 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $result[$row['service1Id']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            's.inscrit' => 1,
            'a.station2Id' => $stationId
        ), 'service2Id');
        foreach ($rowset as $row) {
            if ($row['service2Id'] == '')
                continue;
            $result[$row['service2Id']]['r2'] = $row['effectif'];
        }
        
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byService()
    {
        // SELECT service1Id serviceId, count(*) FROM `sbm_t_affectations` WHERE millesime=2014 GROUP BY service1Id
        $result = array();
        $rowset = $this->requeteSrv('service1Id', array(
            'inscrit' => 1
        ), 'service1Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteSrv2('service', array(
            'inscrit' => 1
        ), 'service2Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byStation()
    {
        // SELECT station1Id stationId, count(*) FROM `sbm_t_affectations` WHERE millesime=2014 GROUP BY station1Id
        $result = array();
        $rowset = $this->requeteSrv('station1Id', array(
            'inscrit' => 1
        ), 'station1Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteSrv2('station', array(
            'inscrit' => 1
        ), 'station2Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byTarif()
    {
        // SELECT tarifId, count(*) FROM sbm_t_scolarites GROUP BY tarifId
        $result = array();
        $group = array('tarifId');
        $filtre = array('s.inscrit' => 1);
        $rowset = $this->requeteCl('tarifId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['tarifId']]['demandes'] = $row['effectif'];
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('tarifId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['tarifId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    public function transporteurByService($transporteurId)
    {
        // SELECT service1Id serviceId, count(*) FROM `sbm_t_affectations` a JOIN `sbm_t_services` s ON a.service1Id=s.serviceId WHERE millesime=2014 AND transporteurId=1 GROUP BY service1Id
        $result = array();
        $rowset = $this->requeteTr1('serviceId', array(
            's.inscrit' => 1,
            'transporteurId' => $transporteurId
        ), 'service1Id');
        foreach ($rowset as $row) {
            $result[$row['serviceId']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteTr2('serviceId', array(
            's.inscrit' => 1,
            'transporteurId' => $transporteurId
        ), 'a.service2Id');
        foreach ($rowset as $row) {
            $result[$row['serviceId']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byTransporteur()
    {
        // SELECT transporteurId, count(*) FROM `sbm_t_affectations` a JOIN sbm_t_services s on a.service1Id=s.serviceId WHERE millesime=2014 GROUP BY transporteurId
        $result = array();
        $rowset = $this->requeteTr1('transporteurId', array(
            's.inscrit' => 1
        ), 'transporteurId');
        foreach ($rowset as $row) {
            $result[$row['transporteurId']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteTr2('transporteurId', array(
            's.inscrit' => 1
        ), 'transporteurId');
        foreach ($rowset as $row) {
            $result[$row['transporteurId']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        // die(var_dump($result));
        return $result;
    }

    /**
     *
     * @param string $colum
     *            une colonne parmi sercice1Id ou service2Id (table affectations)
     * @param string|array|Where $where
     *            description de la requête dans un tableau associatif
     *            
     * @return ResultSet
     */
    private function requeteWith($column, $where, $group)
    {
        $where['s.millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->tableName['scolarites']
        ))
            ->join(array(
            'a' => $this->tableName['affectations']
        ), 'a.millesime=s.millesime AND a.eleveId=s.eleveId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteWith2($by, $conditions, $group)
    {
        $select1 = new Select();
        $select1->from(array(
            'a1' => $this->tableName['affectations']
        ))->where(array(
            'millesime' => $this->millesime,
            'correspondance' => 2
        ));
        $column = $by . '2Id';
        $foreign = $by . '1Id';
        $jointure = "a.millesime=correspondances.millesime AND a.eleveId=correspondances.eleveId AND a.trajet=correspondances.trajet AND a.jours=correspondances.jours AND a.sens=correspondances.sens AND a.$column=correspondances.$foreign";
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime)->isNull('correspondances.millesime');
        foreach ($conditions as $key => $value) {
            $where->equalTo($key, $value);
        }
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->tableName['scolarites']
        ))
            ->join(array(
            'a' => $this->tableName['affectations']
        ), 'a.millesime=s.millesime AND a.eleveId=s.eleveId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->join(array(
            'correspondances' => $select1
        ), $jointure, array(), Select::JOIN_LEFT)
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     *
     * @param string $column            
     * @param array $where            
     * @param string $group            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function requeteSrv($column, $where, $group)
    {
        $where['a.millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
            ->columns(array(
            'column' => $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteSrv2($by, $conditions, $group)
    {
        $select1 = new Select();
        $select1->from($this->tableName['affectations'])->where(array(
            'millesime' => $this->millesime,
            'correspondance' => 2
        ));
        $column = $by . '2Id';
        $foreign = $by . '1Id';
        $jointure = "a.millesime=correspondances.millesime AND a.eleveId=correspondances.eleveId AND a.trajet=correspondances.trajet AND a.jours=correspondances.jours AND a.sens=correspondances.sens AND a.$column=correspondances.$foreign";
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull('correspondances.millesime');
        foreach ($conditions as $key => $value) {
            $where->equalTo($key, $value);
        }
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            'correspondances' => $select1
        ), $jointure, array(), Select::JOIN_LEFT)
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
            ->columns(array(
            'column' => $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group("a.$group");
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteTr1($column, $where, $group)
    {
        $where['a.millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->columns(array())
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
            ->join(array(
            'ser' => $this->tableName['services']
        ), 'a.service1Id=ser.serviceId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        //die($this->getSqlString($select));
        return $statement->execute();
    }

    private function requeteTr2($column, $conditions, $group)
    {
        $select1 = new Select();
        $select1->from(array(
            'a1' => $this->tableName['affectations']
        ))->where(array(
            'a1.millesime' => $this->millesime,
            'correspondance' => 2
        ));
        $jointure = "a.millesime=correspondances.millesime AND a.eleveId=correspondances.eleveId AND a.trajet=correspondances.trajet AND a.jours=correspondances.jours AND a.sens=correspondances.sens AND a.service2Id=correspondances.service1Id";
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull('correspondances.millesime');
        foreach ($conditions as $key => $value) {
            $where->equalTo($key, $value);
        }
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->columns(array())
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
            ->join(array(
            'ser' => $this->tableName['services']
        ), 'a.service2Id=ser.serviceId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->join(array(
            'correspondances' => $select1
        ), $jointure, array(), Select::JOIN_LEFT)
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteTrSrv($rang, $column, $where, $group)
    {
        $where['millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            's' => $this->tableName['services']
        ), sprintf('a.service%sId=s.serviceId', $rang), array(
            'serviceId' => $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCl($column, $filtre, $group, $transportes = false)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->tableName['scolarites']
        ))
            ->columns(array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($this->arrayToWhere($where, $filtre))
            ->group($group);
        if ($transportes) {
            $select->join(array(
                'a' => $this->tableName['affectations']
            ), 'a.millesime = s.millesime AND a.eleveId = s.eleveId', array());
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCir($rang, $column, $filtre, $group)
    {
        $where = new Where();
        $where->equalTo('c.millesime', $this->millesime);
        $on = sprintf('c.millesime=a.millesime AND c.serviceId=a.service%sId AND c.stationId=a.station%sId', $rang, $rang);
        $select = $this->sql->select();
        $select->from(array(
            'c' => $this->tableName['circuits']
        ))
            ->join(array(
            'a' => $this->tableName['affectations']
        ), $on, array(
            'effectif' => new Expression('count(*)')
        ))
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime = a.millesime AND s.eleveId = a.eleveId', array())
            ->columns(array(
            'column' => $column
        ))
            ->where($this->arrayToWhere($where, $filtre))
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCom($rang, $column, $filtre, $group)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
        $select = $this->sql->select();
        switch ($rang) {
            case 1:
            case 2:
                $on = sprintf('e.responsable%sId=r.responsableId', $rang);
                $select->from(array(
                    'e' => $this->tableName['eleves']
                ))
                    ->join(array(
                    's' => $this->tableName['scolarites']
                ), 'e.eleveId=s.eleveId', array())
                    ->join(array(
                    'r' => $this->tableName['responsables']
                ), $on, array(
                    'column' => $column
                ))
                    ->columns(array(
                    'effectif' => new Expression('count(*)')
                ))
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            case 3:
                $select->from(array(
                    's' => $this->tableName['scolarites']
                ))
                    ->columns(array(
                    'column' => $column,
                    'effectif' => new Expression('count(*)')
                ))
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group)
                    ->having(function (Having $where) {
                    $where->isNotNull('communeId');
                });
                break;
            case 4:
            case 5:
                $select->from(array(
                    's' => $this->tableName['scolarites']
                ))
                    ->join(array(
                    'a' => $this->tableName['affectations']
                ), 'a.millesime = s.millesime AND a.eleveId=s.eleveId', array())
                    ->join(array(
                    'r' => $this->tableName['responsables']
                ), 'r.responsableId = a.responsableId', array(
                    'column' => $column
                ))
                    ->columns(array(
                    'effectif' => new Expression('count(*)')
                ))
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            case 6:
                $select->from(array(
                    's' => $this->tableName['scolarites']
                ))
                    ->join(array(
                    'a' => $this->tableName['affectations']
                ), 'a.millesime = s.millesime AND a.eleveId=s.eleveId', array())
                    ->columns(array(
                    'column' => $column,
                    'effectif' => new Expression('count(*)')
                ))
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            default:
                throw new \SbmGestion\Model\Db\Service\Exception(__METHOD__ . ' - Mauvais argument `rang`.');
                break;
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
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