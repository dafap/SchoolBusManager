<?php
/**
 * Partie commune aux classes de ce namespace
 *
 * @project sbm
 * @package SbmPortail/src/Model/Db/Service/Query
 * @filesource AbstractPortailQuery.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\Db\Service\Query;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\Model\Point;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

abstract class AbstractPortailQuery extends AbstractQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

    /**
     *
     * @var bool
     */
    protected $sansimpayes;

    /**
     *
     * @var ProjectionInterface
     */
    protected $projection;

    /**
     *
     * @param \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface $projection
     * @return self
     */
    public function setProjection(ProjectionInterface $projection): self
    {
        $this->projection = $projection;
        return $this;
    }

    /**
     *
     * @param bool $sansimpayes
     * @return self
     */
    public function setSansImpayes(bool $sansimpayes): self
    {
        $this->sansimpayes = $sansimpayes;
        return $this;
    }

    /**
     * Renvoie un tableau de points
     *
     * @return array
     */
    public function etablissementsPourCarte()
    {
        $resultset = $this->renderResult($this->selectEtablissementsPourCarte());
        $ptEtablissements = [];
        foreach ($resultset as $etablissement) {
            $etablissement->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $pt = new Point($etablissement->x, $etablissement->y);
            $pt->setAttribute('etablissement', $etablissement);
            $ptEtablissements[] = $this->projection->xyzVersgRGF93($pt);
        }
        return $ptEtablissements;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    abstract protected function selectEtablissementsPourCarte(): Select;

    /**
     * Renvoie un tableau de points
     *
     * @return array
     */
    public function stationsPourCarte()
    {
        $resultset = $this->renderResult($this->selectStationsPourCarte());
        $keysService = [
            'serviceId',
            'service',
            'ligneId',
            'sens',
            'moment',
            'ordre',
            'passage',
            'horaireD'
        ];
        $arrayStations = [];
        foreach ($resultset as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $arStation = $row->getArrayCopy();
            $arService = [];
            foreach ($keysService as $key) {
                $arService[$key] = $row->{$key};
                unset($arStation[$key]);
            }
            $aoService = new \ArrayObject($arService, \ArrayObject::ARRAY_AS_PROPS);
            if (array_key_exists($row->stationId, $arrayStations)) {
                // ajout du service et de l'horaire
                $arrayStations[$row->stationId]->services[] = $aoService;
            } else {
                // création d'un élément
                $arStation['services'][] = $aoService;
                $arrayStations[$row->stationId] = new \ArrayObject($arStation,
                    \ArrayObject::ARRAY_AS_PROPS);
            }
        }
        $ptStations = [];
        foreach ($arrayStations as $station) {
            $station->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $pt = new Point($station->x, $station->y);
            $pt->setAttribute('station', $station);
            $ptStations[] = $this->projection->xyzVersgRGF93($pt);
        }
        return $ptStations;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    abstract protected function selectStationsPourCarte(): Select;

    /**
     * Renvoie un tableau de points ayant un attribut 'station' (objet).
     * Les propriétés de cet attribut 'station' sont :
     * - x
     * - y
     * - stationId
     * - nom
     * - alias
     * - ouverte
     * - codePostal
     * - commune
     * - lacommune
     * - laposte
     * - services (voir ci-dessous)
     * - couleur
     * La propriété 'services' est un tableau associatif dont la clé est stationId et dont
     * les enregistrements sont des objets composés des propriétés suivantes :
     * - serviceId
     * - service
     * - ligneId
     * - sens
     * - moment
     * - ordre
     * - passage
     * - horaireD
     * Les stations du circuit ont la couleur 1 sauf la stationId qui a la couleur 2.
     *
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @param int $stationId
     * @return array
     */
    public function circuitPourCarte(string $ligneId, int $sens, int $moment, int $ordre,
        int $stationId = 0)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('ligneId', $ligneId)
            ->equalTo('sens', $sens)
            ->equalTo('moment', $moment)
            ->equalTo('ordre', $ordre);
        $resultset = $this->renderResult($this->selectCircuitPourCarte($where));
        $keysService = [
            'serviceId',
            'service',
            'ligneId',
            'sens',
            'moment',
            'ordre',
            'passage',
            'horaireD'
        ];
        $arrayStations = [];
        foreach ($resultset as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $arStation = $row->getArrayCopy();
            $arService = [];
            foreach ($keysService as $key) {
                $arService[$key] = $row->{$key};
                unset($arStation[$key]);
            }
            $aoService = new \ArrayObject($arService, \ArrayObject::ARRAY_AS_PROPS);
            if (array_key_exists($row->stationId, $arrayStations)) {
                // ajout du service et de l'horaire
                $arrayStations[$row->stationId]->services[] = $aoService;
            } else {
                // création d'un élément
                $arStation['couleur'] = $row->stationId == $stationId ? 2 : 1;
                $arStation['services'][] = $aoService;
                $arrayStations[$row->stationId] = new \ArrayObject($arStation,
                    \ArrayObject::ARRAY_AS_PROPS);
            }
        }
        $ptStations = [];
        foreach ($arrayStations as $station) {
            $station->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $pt = new Point($station->x, $station->y);
            $pt->setAttribute('station', $station);
            $ptStations[] = $this->projection->xyzVersgRGF93($pt);
        }
        return $ptStations;
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return \Zend\Db\Sql\Select
     */
    protected function selectCircuitPourCarte(Where $where): Select
    {
        return $this->sql->select()
            ->columns([
            'stationId',
            'x',
            'y',
            'nom',
            'alias',
            'ouverte'
        ])
            ->from([
            'sta' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes')
        ], 'com.communeId = sta.communeId',
            [
                'codePostal',
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits', 'table')
        ], 'cir.stationId = sta.stationId',
            [
                'serviceId' => new Literal($this->getSqlEncodeServiceId('cir')),
                'service' => new Literal(
                    $this->getSqlSemaineLigneHoraireSens('semaine', 'ligneId', 'horaireD')),
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'passage',
                'horaireD'
            ])
            ->where($where)
            ->order('horaireD');
    }

    /**
     * Renvoie la liste des élèves correspondant au Where indiqué
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function listeEleves(Where $where = null, array $order = [])
    {
        return $this->renderResult($this->selectEleves($where, $order));
    }

    /**
     * Renvoie un paginator de la liste des élèves correspondant au Where indiqué
     *
     * @param Where $where
     * @param array $order
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorEleves(Where $where, array $order = [])
    {
        return $this->paginator($this->selectEleves($where, $order));
    }

    /**
     * Requête servant à construire la liste des élèves
     *
     * @param \Zend\Db\Sql\Where $where
     * @param array $order
     * @return \Zend\Db\Sql\Select
     */
    protected function selectEleves(Where $where = null, array $order): Select
    {
        if (is_null($where)) {
            $where = new Where();
            $where->equalTo('sco.millesime', $this->millesime)->literal('sco.inscrit = 1');
        }
        $adresseElv = function ($part_adresse) {
            switch ($part_adresse) {
                case 'adresseL1':
                    return 'IFNULL(sco.chez, r1.adresseL1)';
                case 'adresseL2':
                    return 'IFNULL(sco.adresseL1, r1.adresseL2)';
                case 'adresseL3':
                    return 'IFNULL(sco.adresseL2, r1.adresseL3)';
                case 'codePostal':
                    return 'IFNULL(sco.codePostal, r1.codePostal)';
                case 'lacommune':
                    return 'IFNULL(comele.alias, comr1.alias)';
            }
        };
        $responsableNomPrenom = function ($rang) {
            return sprintf(
                'CASE WHEN isnull(r%1$d.responsableId) THEN "" ELSE CONCAT(r%1$d.nom, " ", r%1$d.prenom) END',
                $rang);
        };
        $select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns(
            [
                'eleveId' => 'eleveId',
                'mailchimp' => 'mailchimp',
                'dateCreation' => 'dateCreation',
                'dateModificationEleve' => 'dateModification',
                'nom_eleve' => 'nom',
                'nomSA' => 'nomSA',
                'prenom_eleve' => 'prenom',
                'prenomSA' => 'prenomSA',
                'dateN' => 'dateN',
                'sexe' => new Literal('IF(sexe = 1,\'masculin\',\'féminin\')'),
                'numero' => 'numero',
                'responsable1Id' => 'responsable1Id',
                'responsable2Id' => 'responsable2Id',
                'adresseL1Elv' => new Literal($adresseElv('adresseL1')),
                'adresseL2Elv' => new Literal($adresseElv('adresseL2')),
                'adresseL3Elv' => new Literal($adresseElv('adresseL3')),
                'codePostalElv' => new Literal($adresseElv('codePostal')),
                'lacommuneElv' => new Literal($adresseElv('lacommune')),
                'etablissement' => new Literal('CONCAT(eta.nom, " - ", cometa.alias)'),
                'responsable1NomPrenom' => new Literal($responsableNomPrenom(1)),
                'responsable2NomPrenom' => new Literal($responsableNomPrenom(2)),
                'selectionEleve' => 'selection',
                'noteEleve' => 'note',
                'servicesR1' => new Literal($this->services(1)),
                'servicesR2' => new Literal($this->services(2))
            ])
            ->join([
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id=r1.responsableId',
            [
                'titreR1' => 'titre',
                'adresseL1R1' => 'adresseL1',
                'adresseL2R1' => 'adresseL2',
                'adresseL3R1' => 'adresseL3',
                'codePostalR1' => 'codePostal',
                'communeIdR1' => 'communeId',
                'telephoneFR1' => 'telephoneF',
                'telephonePR1' => 'telephoneP',
                'telephoneTR1' => 'telephoneT',
                'emailR1' => 'email',
                'x1' => 'x',
                'y1' => 'y'
            ])
            ->join([
            'comr1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r1.communeId = comr1.communeId',
            [
                'communeR1' => 'nom',
                'lacommuneR1' => 'alias',
                'laposteR1' => 'alias_laposte'
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'millesime' => 'millesime',
                'selectionScolarite' => 'selection',
                'dateInscription' => 'dateInscription',
                'dateModificationScolarite' => 'dateModification',
                'etablissementId' => 'etablissementId',
                'classeId' => 'classeId',
                'chez' => 'chez',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'codePostal' => 'codePostal',
                'communeId' => 'communeId',
                'x' => 'x',
                'y' => 'y',
                'distanceR1' => 'distanceR1',
                'distanceR2' => 'distanceR2',
                'dateEtiquetteR1' => 'dateEtiquetteR1',
                'dateEtiquetteR2' => 'dateEtiquetteR2',
                'dateCarteR1' => 'dateCarteR1',
                'dateCarteR2' => 'dateCarteR2',
                'inscrit' => 'inscrit',
                'gratuit' => 'gratuit',
                'paiementR1' => 'paiementR1',
                'paiementR2' => 'paiementR2',
                'duplicataR1' => 'duplicataR1',
                'duplicataR2' => 'duplicataR2',
                'fa' => 'fa',
                'anneeComplete' => 'anneeComplete',
                'subventionR1' => 'subventionR1',
                'subventionR2' => 'subventionR2',
                'demandeR1' => 'demandeR1',
                'demandeR2' => 'demandeR2',
                'dateDemandeR2' => 'dateDemandeR2',
                'accordR1' => 'accordR1',
                'accordR2' => 'accordR2',
                'internet' => 'internet',
                'district' => 'district',
                'derogation' => 'derogation',
                'dateDebut' => 'dateDebut',
                'dateFin' => 'dateFin',
                'joursTransportR1' => 'joursTransportR1',
                'joursTransportR2' => 'joursTransportR2',
                'subventionTaux' => 'subventionTaux',
                'grilleTarifR1' => 'grilleTarifR1',
                'grilleCodeR1' => 'grilleTarifR1',
                'reductionR1' => 'reductionR1',
                'grilleTarifR2' => 'grilleTarifR2',
                'grilleCodeR2' => 'grilleTarifR2',
                'reductionR2' => 'reductionR2',
                'tarifId' => 'tarifId',
                'regimeId' =>'regimeId',
                'regime' => new Literal('IF(regimeId = 1,\'interne\',\'DP\')'),
                'motifDerogation' => 'motifDerogation',
                'motifRefusR1' => 'motifRefusR1',
                'motifRefusR2' => 'motifRefusR2',
                'commentaire' => 'commentaire'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId',
            [
                'xeta' => 'x',
                'yeta' => 'y',
                'niveauEtablissement' => 'niveau'
            ])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = cometa.communeId', [])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
            'niveau' => 'niveau',
            'classe' => 'nom'
        ])
            ->join([
            'ori1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori1.stationId = sco.stationIdR1', [
            'origine1' => 'nom'
        ])
            ->join([
            'cor1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor1.communeId = ori1.communeId',
            [
                'communeOrigine1' => 'nom',
                'lacommuneOrigine1' => 'alias',
                'laposteOrigine1' => 'alias_laposte'
            ])
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable2Id=r2.responsableId',
            [
                'titreR2' => 'titre',
                'adresseL1R2' => 'adresseL1',
                'adresseL2R2' => 'adresseL2',
                'adresseL3R2' => 'adresseL3',
                'codePostalR2' => 'codePostal',
                'communeIdR2' => 'communeId',
                'telephoneFR2' => 'telephoneF',
                'telephonePR2' => 'telephoneP',
                'telephoneTR2' => 'telephoneT',
                'emailR2' => 'email',
                'x2' => 'x',
                'y2' => 'y'
            ], Select::JOIN_LEFT)
            ->join([
            'comr2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId = comr2.communeId',
            [
                'communeR2' => 'nom',
                'lacommuneR2' => 'alias',
                'laposteR2' => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->join([
            'ori2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori2.stationId = sco.stationIdR2', [
            'origine2' => 'nom'
        ], Select::JOIN_LEFT)
            ->join([
            'cor2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor2.communeId = ori2.communeId',
            [
                'communeOrigine2' => 'nom',
                'lacommuneOrigine2' => 'alias',
                'laposteOrigine2' => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->join([
            'comele' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sco.communeId = comele.communeId', [], SELECT::JOIN_LEFT);
        $this->joinAffectations($select, 1)->joinAffectations($select, 2);
        $select->where($where)
            ->group('ele.eleveId')
            ->order($order);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Modifie le $select en joignant les tables affectations, circuits et stations du
     * $trajet indiqué.
     *
     * @param \Zend\Db\Sql\Select $select
     * @param int $trajet
     * @return self
     */
    protected function joinAffectations(Select $select, int $trajet)
    {
        $subselect = $this->sql->select($this->db_manager->getCanonicName('affectations'))
            ->where((new Where())->equalTo('trajet', $trajet));
        $select->join([
            "affR$trajet" => $subselect
        ], "affR$trajet.millesime = sco.millesime And affR$trajet.eleveId = sco.eleveId",
            [], Select::JOIN_LEFT)
            ->join([
            "cir1R$trajet" => $this->db_manager->getCanonicName('circuits')
        ], $this->jointureAffectationsCircuits($trajet, 1), [], Select::JOIN_LEFT)
            ->join([
            "sta1R$trajet" => $this->db_manager->getCanonicName('stations')
        ], "cir1R$trajet.stationId = sta1R$trajet.stationId",
            [
                "station1R$trajet" => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                "comsta1R$trajet" => $this->db_manager->getCanonicName('communes', 'table')
            ], "comsta1R$trajet.communeId = sta1R$trajet.communeId",
            [
                "communeStation1R$trajet" => 'nom',
                "lacommuneStation1R$trajet" => 'alias',
                "laposteStation1R$trajet" => 'alias_laposte'
            ], Select::JOIN_LEFT)
            ->join([
            "cir2R$trajet" => $this->db_manager->getCanonicName('circuits')
        ], $this->jointureAffectationsCircuits($trajet, 2), [], Select::JOIN_LEFT)
            ->join([
            "sta2R$trajet" => $this->db_manager->getCanonicName('stations')
        ], "cir2R$trajet.stationId = sta2R$trajet.stationId",
            [
                "station2R$trajet" => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                "comsta2R$trajet" => $this->db_manager->getCanonicName('communes', 'table')
            ], "comsta2R$trajet.communeId = sta2R$trajet.communeId",
            [
                "communeStation2R$trajet" => 'nom',
                "lacommuneStation2R$trajet" => 'alias',
                "laposteStation2R$trajet" => 'alias_laposte'
            ], Select::JOIN_LEFT);
        return $this;
    }

    private function jointureAffectationsCircuits(int $trajet, int $n)
    {
        return implode(' And ',
            [
                sprintf('affR%1$d.millesime = cir%2$dR%1$d.millesime', $trajet, $n),
                sprintf('affR%1$d.ligne1Id = cir%2$dR%1$d.ligneId', $trajet, $n),
                sprintf('affR%1$d.sensligne1 = cir%2$dR%1$d.sens', $trajet, $n),
                sprintf('affR%1$d.moment = cir%2$dR%1$d.moment', $trajet, $n),
                sprintf('affR%1$d.ordreligne1 = cir%2$dR%1$d.ordre', $trajet, $n),
                sprintf('affR%1$d.station%2$dId = cir%2$dR%1$d.stationId', $trajet, $n)
            ]);
    }

    /**
     * Renvoie une expression Sql
     *
     * @param int $trajet
     * @return string
     */
    private function services(int $trajet): string
    {
        $descripteur = sprintf(
            "IF(ISNULL(%s), NULL, CONCAT_WS(' ',%s, %s, %s, %s, %s, %s, %s, %s))",
            "affR$trajet.eleveId", $this->getSqlSemaine("affR$trajet.jours"),
            "affR$trajet.ligne1Id", "DATE_FORMAT(cir1R$trajet.horaireD,'%H:%i')",
            "sta1R$trajet.nom", "comsta1R$trajet.alias",
            "DATE_FORMAT(cir2R$trajet.horaireD,'%H:%i')", "sta2R$trajet.nom",
            "comsta2R$trajet.alias");
        $services = sprintf(
            'GROUP_CONCAT(DISTINCT %2$s ORDER BY affR%1$d.trajet ASC, cir1R%1$d.horaireD ASC, affR%1$d.jours DESC SEPARATOR " | ")',
            $trajet, $descripteur);
        return $services;
    }
}