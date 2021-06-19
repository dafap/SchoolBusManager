<?php
/**
 * Mise à jour des services desservant un établissement scolaire
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Etablissement
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmCommun\Arlysere\Etablissement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\PredicateInterface;
use Zend\Db\Sql\Predicate\Predicate;

class Services extends AbstractQuery
{

    // Condition horaire avec prise en compte du temps de trajet à pied dans
    // l'enchainement
    protected const COND_HORAIRE_TEMPS = 'SEC_TO_TIME(TIME_TO_SEC(%s) + TIME_TO_SEC(%s)) < %s';

    protected function init()
    {
    }

    public function updateServices(string $etablissementId)
    {
        $select = $this->selectServices($etablissementId);
        $resultset = $this->renderResult($select);
        $tEtablissementsServices = $this->db_manager->get(
            'Sbm\Db\Table\EtablissementsServices');
        foreach ($resultset as $service) {
            try {
                $objData = $tEtablissementsServices->getObjdata();
                $objData->exchangeArray($service->getArrayCopy());
                $tEtablissementsServices->saveRecord($objData);
            } catch (\Exception $e) {
                //var_dump($e->getMessage());
            }
        }
    }

    private function selectServices(string $etablissementId)
    {
        $where = new Where(
            [
                $this->getConditionsMatin($etablissementId),
                $this->getConditionsMidi($etablissementId),
                $this->getConditionsAMidi($etablissementId),
                $this->getConditionsSoir($etablissementId)
            ], Where::COMBINED_BY_OR);

        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'etablissementId'
        ])
            ->from(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ])
            ->join(
            [
                'etasta' => $this->db_manager->getCanonicName('etablissements-stations',
                    'table')
            ], 'etasta.etablissementId = eta.etablissementId', [])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits', 'table')
        ], 'cir.stationId = etasta.stationId',
            [
                'millesime',
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'stationId'
            ])
            ->where($where)
            ->order([
            'cir.ligneId',
            'cir.sens',
            'cir.moment',
            'cir.ordre'
        ]);
    }

    /**
     * Aller
     *
     * @param string $etablissementId
     * @return \Zend\Db\Sql\Predicate\PredicateInterface
     */
    private function getConditionsMatin(string $etablissementId): PredicateInterface
    {
        $horaireDescente = 'cir.horaireA';
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hMatin';
        $expression = sprintf(self::COND_HORAIRE_TEMPS, $horaireDescente, $tempsTrajetPied,
            $horaireEtab);
        $predicate = new Predicate(null, Predicate::COMBINED_BY_AND);
        $predicate->equalTo('cir.millesime', $this->millesime)
            ->equalTo('eta.etablissementId', $etablissementId)
            ->literal('cir.moment = 1')
            ->literal($expression);
        return $predicate;
    }

    /**
     * Retour
     *
     * @param string $etablissementId
     * @return \Zend\Db\Sql\Predicate\PredicateInterface
     */
    private function getConditionsMidi(string $etablissementId): PredicateInterface
    {
        $horaireMontee = 'cir.horaireD';
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hMidi';
        $expression = sprintf(self::COND_HORAIRE_TEMPS, $horaireEtab, $tempsTrajetPied,
            $horaireMontee);
        $predicate = new Predicate(null, Predicate::COMBINED_BY_AND);
        $predicate->equalTo('cir.millesime', $this->millesime)
            ->equalTo('eta.etablissementId', $etablissementId)
            ->literal('cir.moment = 2')
            ->literal($expression);
        return $predicate;
    }

    /**
     * Aller
     *
     * @param string $etablissementId
     * @return \Zend\Db\Sql\Predicate\PredicateInterface
     */
    private function getConditionsAMidi(string $etablissementId): PredicateInterface
    {
        $horaireDescente = 'cir.horaireA';
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hAMidi';
        $expression = sprintf(self::COND_HORAIRE_TEMPS, $horaireDescente, $tempsTrajetPied,
            $horaireEtab);
        $predicate = new Predicate(null, Predicate::COMBINED_BY_AND);
        $predicate->equalTo('cir.millesime', $this->millesime)
            ->equalTo('eta.etablissementId', $etablissementId)
            ->literal('cir.moment = 4')
            ->literal($expression);
        return $predicate;
    }

    /**
     * Retour
     *
     * @param string $etablissementId
     * @return \Zend\Db\Sql\Predicate\PredicateInterface
     */
    private function getConditionsSoir(string $etablissementId): PredicateInterface
    {
        $horaireMontee = 'cir.horaireD';
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hSoir';
        $expression = sprintf(self::COND_HORAIRE_TEMPS, $horaireEtab, $tempsTrajetPied,
            $horaireMontee);
        $predicate = new Predicate(null, Predicate::COMBINED_BY_AND);
        $predicate->equalTo('cir.millesime', $this->millesime)
            ->equalTo('eta.etablissementId', $etablissementId)
            ->literal('cir.moment = 3')
            ->literal($expression);
        return $predicate;
    }
}