<?php
/**
 * Recherche d'itinéraires pour un élève interne
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Itineraire
 * @filesource Interne.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Arlysere\Itineraire;

use SbmCommun\Arlysere\Exception;
use Zend\Db\Sql\Where;

class Interne extends AbstractItineraire
{

    // use \SbmCommun\Model\Traits\DebugTrait;

    // du lundi
    const JOURS_MATIN = 1;

    // vendredi
    const JOURS_SOIR = 16;

    // dimanche
    const DIMANCHE_SOIR = 64;

    // moments pour lesquels on doit rechercher un itinéraire
    protected const MOMENTS = [
        1,
        3,
        5
    ];

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::init()
     */
    protected function init()
    {
        parent::init();
        $this->setRegimeId(1);
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::rangService()
     */
    protected function rangService()
    {
        return 'rang';
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\ItineraireInterface::run()
     */
    public function run()
    {
        $this->process(self::MOMENTS);
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::getMoment()
     */
    protected function getMoment(int $moment = 0): int
    {
        return $moment ?: $this->moment;
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::getFiltreJours()
     */
    protected function getFiltreJours(int $moment = 0): int
    {
        switch ($this->getMoment($moment)) {
            case 1:
                return self::JOURS_MATIN;
                break;
            case 3:
                return self::JOURS_SOIR;
                break;
            default:
                return self::DIMANCHE_SOIR;
                break;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::prepareItineraire()
     */
    protected function prepareItineraire(int $nb_troncons)
    {
        parent::prepareItineraire($nb_troncons);
        switch ($this->moment) {
            case 1:
                $this->select->where($this->getConditionsMatin($nb_troncons));
                break;
            case 3:
                $this->select->where($this->getConditionsSoir($nb_troncons));
                break;
            case 5:
                $this->select->where($this->getConditionsDimanche($nb_troncons));
                break;
            default:
                throw new Exception\OutOfBoundsException(
                    __METHOD__ .
                    sprintf(
                        " \nCe moment (%d) n\'est pas compatible avec un élève interne",
                        $this->moment));
                break;
        }
        /*
         * die($this->getSqlString($this->select));
         * $this->debugLog([
         * __METHOD__ => $this->getSqlString($this->select)
         * ]);
         */
        return $this;
    }

    /**
     * ALLER Matin
     * Nécessite que soient initialisées les propriétés suivantes :<ul>
     * <li>stationId (station origine pour cet élève et ce responsable)</li>
     * <li>les propriétés nécessaires à la méthode getConditionsCommunes()</li></ul>
     *
     * @param int $nb_troncons
     * @return \Zend\Db\Sql\Where
     */
    protected function getConditionsMatin(int $nb_troncons): Where
    {
        // condition horaire à l'arrivée à l'établissement
        $horaireDescente = sprintf('tr%d.horaireA', $nb_troncons);
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hMatin';
        $expression = sprintf(self::COND_HORAIRE_TEMPS, $horaireDescente, $tempsTrajetPied,
            $horaireEtab);
        // colonne station origine
        $stationOrigine = 'tr1.origine_stationId';
        // objet Where à renvoyer
        return $this->getConditionsCommunes($nb_troncons)
            ->literal($expression)
            ->equalTo($stationOrigine, $this->stationId);
    }

    /**
     * RETOUR Soir
     * Nécessite que soient initialisées les propriétés suivantes :<ul>
     * <li>stationId (station de destination pour cet élève et ce responsable)</li>
     * <li>les propriétés nécessaires à la méthode getConditionsCommunes()</li></ul>
     *
     * @param int $nb_troncons
     * @return \Zend\Db\Sql\Where
     */
    protected function getConditionsSoir(int $nb_troncons): Where
    {
        // condition horaire au départ de l'établissement
        $horaireMontee = 'tr1.horaireD';
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hSoir';
        $expression = sprintf(self::COND_HORAIRE_TEMPS, $horaireEtab, $tempsTrajetPied,
            $horaireMontee);
        // colonne station de destination
        $stationDestination = sprintf('tr%d.destination_stationId', $nb_troncons);
        // objet Where à renvoyer
        return $this->getConditionsCommunes($nb_troncons)
            ->literal($expression)
            ->equalTo($stationDestination, $this->stationId);
    }

    /**
     * ALLER Dimanche soir
     * PAS DE CONDITION HORAIRE à l'arrivée à l'établissement.
     * Nécessite que soient initialisées les propriétés suivantes :<ul>
     * <li>stationId (station origine pour cet élève et ce responsable)</li>
     * <li>les propriétés nécessaires à la méthode getConditionsCommunes()</li></ul>
     *
     * @param int $nb_troncons
     * @return \Zend\Db\Sql\Where
     */
    protected function getConditionsDimanche(int $nb_troncons): Where
    {
        // colonne station origine
        $stationOrigine = 'tr1.origine_stationId';
        // objet Where à renvoyer
        return $this->getConditionsCommunes($nb_troncons)->equalTo($stationOrigine,
            $this->stationId);
    }

    /**
     * Nécessite que soient initialisées les propriétés suivantes :<ul>
     * <li>millesime (ce qui est fait par AbstractQuery)</li>
     * <li>etablissementId (établissement scolaire de cet élève)</li>
     * <li>jours (jours de transport demandés pour cet élève)</li></ul>
     *
     * @param int $nb_troncons
     * @return \Zend\Db\Sql\Where
     */
    protected function getConditionsCommunes(int $nb_troncons): Where
    {
        $where = new Where();
        $where->equalTo('eta.etablissementId', $this->etablissementId);
        for ($num_tr = 1; $num_tr <= $nb_troncons; $num_tr ++) {
            $where->literal(sprintf('(tr%d.semaine & %d) <> 0', $num_tr, $this->jours));
            if ($num_tr > 1) {
                // condition horaire de correspondance entre 2 tronçons
                $horaireDescente = sprintf('tr%d.horaireA', $num_tr - 1);
                $tempsTrajetPied = sprintf('tr%d.temps', $num_tr);
                $horaireMontee = sprintf('tr%d.horaireD', $num_tr);
                $expression = sprintf(self::COND_HORAIRE_TEMPS, $horaireDescente,
                    $tempsTrajetPied, $horaireMontee);
                $where->literal($expression);
            }
        }
        return $where;
    }
}