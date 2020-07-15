<?php
/**
 * Listes d'élèves pour un ou plusieurs critères donnés
 *
 * Il y a deux méthodes pour obtenir le résulatat de la requête (queryGroup...) et
 * deux similaires pour un paginator avec la même requête (paginatorGroup...).
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use SbmGestion\Model\Db\Service\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Liste extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     * La nouvelle version n'initialise plus la propriété $select (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->sql = new Sql($this->db_manager->getDbAdapter());
        return $this;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un filtre donnés et dans l'ordre
     * demandé
     *
     * @param int $millesime
     * @param array $filtre
     * @param string|array $order
     * @param string $by
     *            laisser vide sauf pour 'service' ou 'station'
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function queryGroup($millesime, $filtre,
        $order = [
            'commune',
            'nom',
            'prenom'
        ], $by = '')
    {
        if ($by == 'service') {
            $select = $this->selectGroupByService($millesime, $filtre, $order);
        } elseif ($by == 'station') {
            $select = $this->selectGroupByStation($millesime, $filtre, $order);
        } else {
            $select = $this->selectGroup($millesime, $filtre, $order);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Renvoie la même liste paginée
     *
     * @param int $millesime
     * @param array $filtre
     * @param string|array $order
     * @param string $by
     *            laisser vide sauf pour 'service' ou 'station'
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorGroup($millesime, $filtre,
        $order = [
            'commune',
            'nom',
            'prenom'
        ], $by = '')
    {
        switch ($by) {
            case 'service':
                $select = $this->selectGroupByService($millesime, $filtre, $order);
                break;
            case 'station':
                $select = $this->selectGroupByStation($millesime, $filtre, $order);
                break;
            case 'tarif':
                $select = $this->selectGroupByScolariteResponsable($millesime,
                    function () use ($filtre) {
                        return new Expression(
                            "IF(sco.demandeR2>0 AND sco.grilleTarifR2 = ?, res.responsableId=ele.responsable2Id,res.responsableId=ele.responsable1Id)",
                            $filtre['grilleTarif']);
                    },
                    \SbmGestion\Model\Db\Filtre\Eleve\Filtre::byGrilleTarif(
                        $filtre['grilleTarif'], $filtre['reduction']), $order);
                break;
            default:
                $select = $this->selectGroup($millesime, $filtre, $order);
                break;
        }
        // die($this->getSqlString($select));
        return new Paginator(new DbSelect($select, $this->db_manager->getDbAdapter()));
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un lot de marché donné le filtre
     *
     * @param int $millesime
     * @param array $filtre
     * @param string|array $order
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function queryGroupParAffectations(int $millesime, array $filtre,
        $order = [
            'serviceId',
            'nom',
            'prenom'
        ])
    {
        $select = $this->selectGroupParAffectations($millesime, $filtre, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorGroupParAffectations(int $millesime, array $filtre,
        $order = [
            'serviceId',
            'nom',
            'prenom'
        ])
    {
        return new Paginator(
            new DbSelect($this->selectGroupParAffectations($millesime, $filtre, $order),
                $this->db_manager->getDbAdapter()));
    }

    /**
     * Renvoi un Select avec les colonnes qui vont bien pour les groupes d'élèves. Le
     * sélect est filtré par le filtre donné. (utilisé dans queryGroup() et dans
     * paginatorGroup())
     *
     * @formatter
     * ATTENTION : pas d'affectation, ni de service, ni de station
     * Les alias des tables sont les suivants :
     * <ul>
     * <li>eleves AS ele</li>
     * <li>scolarites AS sco</li>
     * <li>etablissements AS eta</li>
     * <li>classes AS cla</li>
     * <li>responsables AS res (lié sur affectations)</li>
     * <li>communes AS comres (pour la commune du responsable)</li>
     * <li>communes AS comsco (pour la commune de l'élève s'il a une adresse perso)</li>
     * <li>elevesphotos AS photos</li>
     * </ul>
     *
     * @param int $millesime
     * @param array $filtre
     * @param string|array $order
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectGroup(int $millesime, array $filtre, $order)
    {
        $where = new Where();
        $where->equalTo('sco.millesime', $millesime);

        $select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ],
            'res.responsableId=ele.responsable1Id OR res.responsableId=ele.responsable2Id',
            [
                'email',
                'responsable' => new Literal(
                    'CONCAT(res.titre, " ", res.nom, " ", res.prenom)')
            ])
            ->join([
            'comres' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'res.communeId=comres.communeId', [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId=sco.eleveId',
            [
                'inscrit',
                'demandeR1',
                'demandeR2',
                'paiementR1',
                'paiementR2',
                'fa',
                'gratuit',
                'dateCarteR1',
                'dateCarteR2'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', [
                'etablissement' => 'nom'
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'comsco' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsco.communeId=sco.communeId', [], Select::JOIN_LEFT)
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId',
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], Select::JOIN_LEFT)
            ->columns(
            [
                'eleveId',
                'nom',
                'prenom',
                'sexe',
                'ga' => new Literal('responsable2Id IS NOT NULL'),
                'adresseL1' => new Literal('IFNULL(sco.adresseL1, res.adresseL1)'),
                'adresseL2' => new Literal(
                    'CASE WHEN sco.adresseL1 IS NULL THEN res.adresseL2 ELSE sco.adresseL2 END'),
                'adresseL3' => new Literal(
                    'CASE WHEN sco.adresseL1 IS NULL THEN res.adresseL3 ELSE "" END'),
                'codePostal' => new Literal('IFNULL(sco.codePostal, res.codePostal)'),
                'commune' => new Literal('IFNULL(comsco.alias, comres.alias)')
            ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($this->arrayToWhere($where, $filtre));

        if (! empty($order)) {
            $select->order($order);
        }
        // die($this->getSqlString($select));
        return $select;
    }

    protected function selectGroupByScolariteResponsable(int $millesime,
        callable $jointureEleveResponsable, array $filtre, $order)
    {
        $where = new Where();
        $where->equalTo('sco.millesime', $millesime);

        $select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId=sco.eleveId',
            [
                'inscrit',
                'paiementR1',
                'paiementR2',
                'fa',
                'gratuit',
                'dateCarteR1',
                'dateCarteR2'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', [
                'etablissement' => 'nom'
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [
            'classe' => 'nom'
        ])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], $jointureEleveResponsable(),
            [
                'email',
                'responsable' => new Literal(
                    'CONCAT(res.titre, " ", res.nom, " ", res.prenom)')
            ])
            ->join([
            'comres' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'res.communeId=comres.communeId', [])
            ->join([
            'comsco' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsco.communeId=sco.communeId', [], Select::JOIN_LEFT)
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId',
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], Select::JOIN_LEFT)
            ->columns(
            [
                'eleveId',
                'nom',
                'prenom',
                'sexe',
                'adresseL1' => new Literal('IFNULL(sco.adresseL1, res.adresseL1)'),
                'adresseL2' => new Literal(
                    'CASE WHEN sco.adresseL1 IS NULL THEN res.adresseL2 ELSE sco.adresseL2 END'),
                'adresseL3' => new Literal(
                    'CASE WHEN sco.adresseL1 IS NULL THEN res.adresseL3 ELSE "" END'),
                'codePostal' => new Literal('IFNULL(sco.codePostal, res.codePostal)'),
                'commune' => new Literal('IFNULL(comsco.alias, comres.alias)')
            ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($this->arrayToWhere($where, $filtre));

        if (! empty($order)) {
            $select->order($order);
        }
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie un Select pour la recherche des élèves par EtablissementService, par Lot ou
     * par Transporteur. (utilisé par queryGroupParAffectations() et par
     * paginatorGroupParAffectations())
     *
     * @formatter
     * ATTENTION
     * Les alias des tables sont les suivants :
     * <ul>
     * <li>eleves AS e</li>
     * <li>scolarites AS s</li>
     * <li>etablissements AS eta</li>
     * <li>classes AS cla</li>
     * <li>affectations AS a (voir la note plus loin)</li>
     * <li>responsables AS r (lié sur affectations)</li>
     * <li>communes AS c (pour la commune du responsable)</li>
     * <li>communes AS d (pour la commune de l'élève s'il a une adresse perso)</li>
     * <li>services AS ser (uniquement le service1Id)</li>
     * <li>transporteur AS tra</li>
     * <li>stations AS sta (uniquement la station1Id)</li>
     * <li>elevesphotos AS photos</li>
     * </ul>
     * La table affectations est remplacée par SELECT FROM affectations qui renomme les
     * champs de la façon suivante :
     * <ul>
     * <li>station1Id devient stationId</li>
     * <li>ligne1Id devient ligneId</li>
     * <li>sensligne1 devient sens</li>
     * <li>ordreligne1 devient ordre</li>
     * </ul>
     *
     * @param int $millesime
     * @param array $filtre
     * @param string|array $order
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectGroupParAffectations(int $millesime, array $filtre, $order)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $millesime);

        $select = $this->sql->select()
            ->from([
            'e' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            's' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'e.eleveId=s.eleveId',
            [
                'inscrit',
                'paiementR1',
                'paiementR2',
                'fa',
                'gratuit',
                'dateCarteR1',
                'dateCarteR2'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 's.etablissementId = eta.etablissementId', [
                'etablissement' => 'nom'
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 's.classeId = cla.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'a' => $this->affectations($millesime)
        ], 'a.millesime=s.millesime And e.eleveId=a.eleveId', [])
            ->join([
            'r' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'r.responsableId=a.responsableId',
            [
                'email',
                'responsable' => new Literal('CONCAT(r.titre, " ", r.nom, " ", r.prenom)')
            ])
            ->join([
            'c' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r.communeId=c.communeId', [])
            ->join([
            'd' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'd.communeId=s.communeId', [], Select::JOIN_LEFT)
            ->join([
            'ser' => $this->db_manager->getCanonicName('services', 'table')
        ],
            implode(' AND ',
                [
                    'ser.ligneId = a.ligneId',
                    'ser.sens = a.sens',
                    'ser.moment = a.moment',
                    'ser.ordre = a.ordre'
                ]), [
                'ligneId',
                'sens',
                'moment',
                'ordre'
            ])
            ->join(
            [
                'tra' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'tra.transporteurId = ser.transporteurId', [
                'transporteur' => 'nom'
            ])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta.stationId = a.stationId', [
            'station' => 'nom'
        ])
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = e.eleveId',
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], Select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $filtre))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(
            [
                'eleveId',
                'nom',
                'prenom',
                'sexe',
                'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
                'adresseL2' => new Literal(
                    'CASE WHEN s.adresseL1 IS NULL THEN r.adresseL2 ELSE s.adresseL2 END'),
                'adresseL3' => new Literal(
                    'CASE WHEN s.adresseL1 IS NULL THEN r.adresseL3 ELSE "" END'),
                'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
                'commune' => new Literal('IFNULL(d.alias, c.alias)')
            ]);

        if (! empty($order)) {
            $select->order($order);
        }
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Reprend la requête obtenue par la méthode selectGroup() en lui rajoutant la table
     * affectations AS aff
     *
     * @param int $millesime
     * @param array $filtre
     * @param string|array $order
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectGroupByService(int $millesime, array $filtre, $order)
    {
        return $this->selectGroup($millesime, $filtre, $order)->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 'aff.millesime=sco.millesime And sco.eleveId=aff.eleveId',
            [
                'moment',
                'ligneId' => 'ligne1Id',
                'sens' => 'sensligne1',
                'ordre' => 'ordreligne1',
                'ligne1Id',
                'sensligne1',
                'ordreligne1',
                'ligne2Id',
                'sensligne2',
                'ordreligne2'
            ], SELECT::JOIN_LEFT);
    }

    /**
     * Reprend la requête obtenue par la méthode selectGroup() en lui rajoutant la table
     * affectations et deux tables stations (AS sta1 liée sur station1Id et AS sta2 liée
     * sur station2Id)
     *
     * @param int $millesime
     * @param array $filtre
     * @param string|array $order
     * @return \Zend\Db\Sql\Select
     */
    protected function selectGroupByStation(int $millesime, array $filtre, $order)
    {
        return $this->selectGroup($millesime, $filtre, $order)
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 'aff.millesime=sco.millesime And sco.eleveId=aff.eleveId', [],
            SELECT::JOIN_LEFT)
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta1.stationId=aff.station1Id', [], Select::JOIN_LEFT)
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta2.stationId=aff.station2Id', [], Select::JOIN_LEFT);
    }

    /**
     * Renvoie les points de montée et les services des élèves pour un millesime donné en
     * tenant compte des correspondances. (pour selectGroupParAffectations)
     *
     * @formatter
     * ATTENTION
     * La table affectations est remplacée par SELECT FROM affectations qui renomme les
     * champs de la façon suivante :
     * <ul>
     * <li>station1Id devient stationId</li>
     * <li>ligne1Id devient ligneId</li>
     * <li>sensligne1 devient sens</li>
     * <li>ordreligne1 devient ordre</li>
     * </ul>
     *
     * @param int $millesime
     *
     * @return \Zend\Db\Sql\Select
     */
    private function affectations(int $millesime)
    {
        $tableAffectations = $this->db_manager->getCanonicName('affectations', 'table');
        $select1 = $this->sql->select($tableAffectations)
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'responsableId',
                'stationId' => 'station1Id',
                'ligneId' => 'ligne1Id',
                'sens' => 'sensligne1',
                'ordre' => 'ordreligne1'
            ])
            ->where([
            'millesime' => $millesime
        ]);

        $select1cor2 = $this->sql->select($tableAffectations)
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'stationId' => 'station1Id',
                'ligneId' => 'ligne1Id',
                'sens' => 'sensligne1',
                'ordre' => 'ordreligne1'
            ])
            ->where(
            (new Where())->equalTo('millesime', $millesime)
                ->greaterThan('correspondance', 1));

        $jointure = [
            "a2.millesime=correspondances.millesime",
            "a2.eleveId=correspondances.eleveId",
            "a2.trajet=correspondances.trajet",
            "a2.jours=correspondances.jours",
            "a2.moment=correspondances.moment",
            "a2.station2Id=correspondances.stationId"
        ];
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $millesime)
            ->isNotNull('ligne2Id')
            ->isNull('correspondances.millesime');
        $select2 = $this->sql->select([
            'a2' => $tableAffectations
        ])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'responsableId',
                'stationId' => 'station2Id',
                'ligneId' => 'ligne2Id',
                'sens' => 'sensligne2',
                'ordre' => 'ordreligne2'
            ])
            ->join([
            'correspondances' => $select1cor2
        ], implode(' AND ', $jointure), [], Select::JOIN_LEFT)
            ->where($where2);

        return $select1->combine($select2);
    }
}