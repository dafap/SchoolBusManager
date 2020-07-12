<?php
/**
 * Déplacement d'élèves d'un service sur un autre en tenant compte de critères
 *
 * L'objet reçoit les critères sous la forme d'un tableau qui est le retour validé
 * du formulaire Deplacement. Les attributs sont :
 * moment               (obligatoire - le même que pour les 2 services ci-dessous)
 * serviceinitial       (obligatoire - encodage des caractéristiques d'un service)
 * servicefinal         (obligatoire - encodage des caractéristiques d'un service)
 * etablissementcommune (facultatif - vide ou tableau de communeId)
 * etablissementniveau  (facultatif - vide ou tableau de niveaux)
 * etablissementId      (facultatif - vide ou tableau de etablissementId)
 * regimeId             (facultatif - 0 pour DP, 1 pour Interne, 2 pour Tout)
 * classeId             (facultatif - vide ou tableau de classeId)
 * classeniveau         (facultatif - vide ou tableau de niveaux)
 * stationId            (facultatif - vide ou tableau de stationId)
 * paiement             (facultatif - 0 pour Impayé, 1 pour Payé, 2 pour Tout)
 * carte                (facultatif - 0 pour Non tirée, 1 pour Dans un lot, 2 pour Tout)
 * cartelot             (facultatif - vide ou tableau de dates de lots à n'utiliser que si carte = 1)
 * dateinscription      (facultatif - vide ou date au format )
 *
 * @project sbm
 * @package SbmCommun/Arlysere
 * @filesource Deplacement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Traits;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;

class Deplacement extends AbstractQuery
{
    use Traits\ServiceTrait;

    /**
     *
     * @var array
     */
    private $args;

    /**
     *
     * @var array
     */
    private $serviceInitial;

    /**
     *
     * @var array
     */
    private $serviceFinal;

    /**
     *
     * @var array
     */
    private $equivalenceStations;

    protected function init()
    {
        $this->equivalenceStations = [];
    }

    /**
     * Méthode lançant l'action de déplacement à partir des paramètres reçus
     *
     * @param array $args
     * @return void
     */
    public function run(array $args)
    {
        $this->args = $args;
        $this->serviceInitial = $this->decodeServiceId($this->args['serviceinitial']);
        $this->serviceFinal = $this->decodeServiceId($this->args['servicefinal']);
        $this->initEquivalenceStations();
        $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        foreach ($this->affectationsConcernees() as $affectation) {
            if (! $this->valideTroncon($affectation['station1Id'],
                $affectation['station2Id'])) {
                continue;
            }
            $this->changeService($affectation);
            $objectAffectation = $tAffectations->getObjData()->exchangeArray($affectation);
            // pour le deteRecord ne pas passer un ObjectDataInterface sinon on perdrait
            // le rend de la correspondance. $affectation est un ArrayObject.
            $where = new Where();
            foreach ($objectAffectation->getIdFieldName() as $field) {
                $where->equalTo($field, $affectation[$field]);
            }
            $tAffectations->deleteRecord($where);
            $tAffectations->saveRecord($objectAffectation);
        }
    }

    private function changeService(&$affectation)
    {
        $affectation['ligne1Id'] = $this->serviceFinal['ligneId'];
        $affectation['sensligne1'] = $this->serviceFinal['sens'];
        $affectation['ordreligne1'] = $this->serviceFinal['ordre'];
    }

    /**
     * Valide le déplacement en vérifiant le tronçon. Les stationId (ou leurs stations
     * jumelles si besoin) doivent être sur les deux lignes. Les extrémités du tronçon
     * sont éventuellements modifiées.
     *
     * @param int $station1Id
     * @param int $station2Id
     * @return bool
     */
    private function valideTroncon(int &$station1Id, int &$station2Id): bool
    {
        if (array_key_exists($station1Id, $this->equivalenceStations) &&
            array_key_exists($station2Id, $this->equivalenceStations)) {
            $station1Id = $this->equivalenceStations[$station1Id];
            $station2Id = $this->equivalenceStations[$station2Id];
            return true;
        }
        return false;
    }

    /**
     * Crée un tableau de la forme stationId => stationId
     */
    private function initEquivalenceStations()
    {
        $where = new Where();
        $where->equalTo('cir1.millesime', $this->millesime)
            ->equalTo('cir1.moment', $this->serviceInitial['moment'])
            ->equalTo('cir1.ligneId', $this->serviceInitial['ligneId'])
            ->equalTo('cir1.sens', $this->serviceInitial['sens'])
            ->equalTo('cir1.ordre', $this->serviceInitial['ordre'])
            ->equalTo('cir2.ligneId', $this->serviceFinal['ligneId'])
            ->equalTo('cir2.sens', $this->serviceFinal['sens'])
            ->equalTo('cir2.ordre', $this->serviceFinal['ordre'])
            ->nest()
            ->equalTo('cir1.stationId ', 'cir2.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)->or->equalTo('jum1.station2Id', 'cir2.stationId',
            Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER)->or->equalTo(
            'jum2.station1Id', 'cir2.stationId', Predicate::TYPE_IDENTIFIER,
            Predicate::TYPE_IDENTIFIER)->unnest();
        $select = $this->sql->select()
            ->columns([
            'cir1stationId' => 'stationId'
        ])
            ->from([
            'cir1' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            'cir2' => $this->db_manager->getCanonicName('circuits')
        ], 'cir1.millesime=cir2.millesime AND cir1.moment=cir2.moment',
            [
                'cir2stationId' => 'stationId'
            ])
            ->join([
            'jum1' => $this->db_manager->getCanonicName('stations-stations')
        ], 'cir1.stationId = jum1.station1Id', [], Select::JOIN_LEFT)
            ->join([
            'jum2' => $this->db_manager->getCanonicName('stations-stations')
        ], 'cir1.stationId = jum2.station2Id', [], Select::JOIN_LEFT)
            ->where($where);
        $resultset = $this->renderResult($select);
        foreach ($resultset as $row) {
            $this->equivalenceStations[$row['cir1stationId']] = $row['cir2stationId'];
        }
    }

    /**
     *
     * @return array
     */
    private function affectationsConcernees()
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime)
            ->equalTo('aff.ligne1Id', $this->serviceInitial['ligneId'])
            ->equalTo('aff.sensligne1', $this->serviceInitial['sens'])
            ->equalTo('aff.moment', $this->serviceInitial['moment'])
            ->equalTo('ordreligne1', $this->serviceInitial['ordre']);
        if (is_array($this->args['stationId'])) {
            $aff_station = $this->serviceInitial['moment'] == 1 ? 'aff.station1Id' : 'aff.station2Id';
            $where->in($aff_station, $this->args['stationId']);
        }
        $sco = $this->conditionsScolarites($where);
        $etab = $this->conditionsEtablissements($where);
        $cla = $this->conditionsClasses($where);
        if ($etab && ! $sco) {
            $sco = true;
        }
        if ($cla && ! $sco) {
            $sco = true;
        }
        // construction de la requête
        $select = $this->sql->select()->from(
            [
                'aff' => $this->db_manager->getCanonicName('affectations')
            ]);
        if ($sco) {
            $select->join([
                'sco' => $this->db_manager->getCanonicName('scolarites')
            ], 'aff.millesime = sco.millesime AND aff.eleveId = sco.eleveId', []);
        }
        if ($etab) {
            $select->join(
                [
                    'eta' => $this->db_manager->getCanonicName('etablissements')
                ], 'sco.etablissementId = eta.etablissementId', []);
        }
        if ($cla) {
            $select->join([
                'cla' => $this->db_manager->getCanonicName('classes')
            ], 'sco.classeId = cla.classeId', []);
        }
        $select->where($where);
        return iterator_to_array($this->renderResult($select));
    }

    private function conditionsScolarites(Where &$where): bool
    {
        $sco = false;
        if (is_array(StdLib::getParam('etablissementId', $this->args, false))) {
            $sco = true;
            $where->in('sco.etablissementId', $this->args['etablissementId']);
        }
        if (StdLib::getParam('regimeId', $this->args, 2) < 2) {
            $sco = true;
            $where->equalTo('sco.regimeId', $this->args['regimeId']);
        }
        if (is_array(StdLib::getParam('classeId', $this->args, false))) {
            $sco = true;
            $where->in('sco.classeId', $this->args['classeId']);
        }
        if (StdLib::getParam('paiement', $this->args, 2) < 2) {
            $sco = true;
            $where->nest()->equalTo('sco.paiementR1', $this->args['paiement'])->or->equalTo(
                'sco.paiementR2', $this->args['paiement'])->unnest();
        }
        if (StdLib::getParam('carte', $this->args, 2) == 0) {
            $sco = true;
            $where->nest()->lessThan('sco.dateCarteR1', 'sco.dateInscription',
                Predicate::TYPE_IDENTIFIER, Predicate::TYPE_IDENTIFIER)->or->lessThan(
                'sco.dateCarteR2', 'sco.dateInscription', Predicate::TYPE_IDENTIFIER,
                Predicate::TYPE_IDENTIFIER)->unnest();
        } elseif (StdLib::getParam('carte', $this->args, 2) == 1) {
            $sco = true;
            if (is_array(StdLib::getParam('cartelot', $this->args, false))) {
                // certains lots
                $where->nest()->in('sco.dateCarteR1', $this->args['cartelot'])->or->in(
                    'sco.dateCarteR2', $this->args['cartelot'])->unnest();
            } else {
                // tous les lots
                $where->nest()->greaterThanOrEqualTo('sco.dateCarteR1',
                    'sco.dateInscription', Predicate::TYPE_IDENTIFIER,
                    Predicate::TYPE_IDENTIFIER)->or->greaterThanOrEqualTo(
                    'sco.dateCarteR2', 'sco.dateInscription', Predicate::TYPE_IDENTIFIER,
                    Predicate::TYPE_IDENTIFIER)->unnest();
            }
        }
        if (StdLib::getParam('dateinscription', $this->args, false)) {
            $sco = true;
            $where->greaterThanOrEqualTo('sco.dateInscription',
                $this->args['dateinscription']);
        }
        return $sco;
    }

    private function conditionsEtablissements(Where $where): bool
    {
        $etab = false;
        if (is_array(StdLib::getParam('etablissementcommune', $this->args, false))) {
            $etab = true;
            $where->in('eta.communeId', $this->args['etablissementcommune']);
        }
        if (is_array(StdLib::getParam('etablissementniveau', $this->args, false))) {
            $etab = true;
            $where->in('eta.niveau', $this->args['etablissementniveau']);
        }
        return $etab;
    }

    private function conditionsClasses(Where $where): bool
    {
        $cla = false;
        if (is_array(StdLib::getParam('classeniveau', $this->args, false))) {
            $cla = true;
            $where->in('cla.niveau', $this->args['classeniveau']);
        }
        return $cla;
    }
}