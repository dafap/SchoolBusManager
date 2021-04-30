<?php
/**
 * Recherche d'itinéraires pour un écolier
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Itineraire
 * @filesource Ecolier.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Arlysere\Itineraire;

use SbmCommun\Arlysere\Exception;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;

class Ecolier extends AbstractItineraire
{

    use \SbmCommun\Model\Traits\DebugTrait;

    // Aller : lundi mardi jeudi vendredi
    protected const JOURS_MATIN = 27;

    // Retour : lundi mardi jeudi vendredi
    protected const JOURS_MIDI = 27;

    // Aller : lundi mardi jeudi vendredi
    protected const JOURS_AMIDI = 27;

    // Retour : lundi mardi jeudi vendredi
    protected const JOURS_SOIR = 27;

    // moments pour lesquels on doit rechercher un itinéraire
    protected const MOMENTS = [
        1,
        2,
        3,
        4
    ];

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::init()
     */
    protected function init()
    {
        parent::init();
        $this->setNiveau(3)->setRegimeId(0);
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
            case 2:
                return self::JOURS_MIDI;
                break;
            case 4:
                return self::JOURS_AMIDI;
                break;
            default:
                return self::JOURS_SOIR;
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
            case 2:
                $this->select->where($this->getConditionsMidi($nb_troncons));
                break;
            case 3:
                $this->select->where($this->getConditionsSoir($nb_troncons));
                break;
            case 4:
                $this->select->where($this->getConditionsAMidi($nb_troncons));
                break;
            default:
                throw new Exception\OutOfBoundsException(
                    __METHOD__ .
                    sprintf(" \nCe moment (%d) n\'est pas compatible avec un écolier",
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
     * Pas d'écolier sur ligne régulière (L%) sauf (L21)
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::getWhereTroncon()
     */
    protected function getWhereTroncon(): Where
    {
        $where = parent::getWhereTroncon();
        return $where->equalTo('lig.internes', $this->regimeId)
            ->nest()
            ->notLike('lig.ligneId', 'L%')->or->equalTo('lig.ligneId', 'L21')->unnest();
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
     * RETOUR Midi
     * Nécessite que soient initialisées les propriétés suivantes :<ul>
     * <li>stationId (station de destination pour cet élève et ce responsable)</li>
     * <li>les propriétés nécessaires à la méthode getConditionsCommunes()</li></ul>
     *
     * @param int $nb_troncons
     * @return \Zend\Db\Sql\Where
     */
    protected function getConditionsMidi(int $nb_troncons): Where
    {
        // condition horaire au départ de l'établissement
        $horaireMontee = 'tr1.horaireD';
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hMidi';
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
     * ALLER Après-midi
     * Nécessite que soient initialisées les propriétés suivantes :<ul>
     * <li>stationId (station origine pour cet élève et ce responsable)</li>
     * <li>les propriétés nécessaires à la méthode getConditionsCommunes()</li></ul>
     *
     * @param int $nb_troncons
     * @return \Zend\Db\Sql\Where
     */
    protected function getConditionsAMidi(int $nb_troncons): Where
    {
        // condition horaire à l'arrivée à l'établissement
        $horaireDescente = sprintf('tr%d.horaireA', $nb_troncons);
        $tempsTrajetPied = 'etasta.temps';
        $horaireEtab = 'eta.hAMidi';
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
            $where->literal(sprintf('(eta.jOuverture & tr%d.semaine) <> 0', $num_tr))
                ->literal(sprintf('tr%d.internes = 0', $num_tr))
                ->literal(sprintf('(tr%d.semaine & %d) <> 0', $num_tr, $this->jours));
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

    /**
     * Les priorités sont dans l'ordre suivant : <ol>
     * <li>Solutions valables pour le plus de jours demandés (pertinenceJours)</li>
     * <li>A l’aller partir le plus tard possible ; au retour arriver le plus tôt possible
     * (montee1.horaireD DESC ou descente%d.horaireA)</li>
     * <li>Départ sur le service de rang le plus bas (ser1.rang)</li>
     * <li>S’il y a des correspondances, priorité à celles qui ont été repérées
     * (descente%d.correspondance DESC où %d va de 1 à nb_de_tronçons - 1)</li>
     * <li>Station desservant l’établissement de rang le plus bas (etasta.rang)</li>
     * <li>S’il y a des correspondances, choisir les services de correspondance de rangs
     * les plus bas possibles (ser%d.rang où %d va de 2 à nb_de_tronçons)</li>
     * <li>En cas de boucles, ordre de passage le plus petit possible</li></ol>
     *
     * @param int $nb_troncons
     * @return string[]
     */
    protected function ordre(int $nb_troncons)
    {
        $ordre = [];
        for ($tr_num = 1, $pertinenceJours = ''; $tr_num <= $nb_troncons; $tr_num ++) {
            $pertinenceJours .= "+ tr$tr_num.pertinenceJours ";
        }
        // 1 - Solutions valables pour le plus de jours demandés (pertinenceJours)
        $ordre[] = new Literal(trim(ltrim($pertinenceJours, '+')));
        if ($this->sensItineraire == self::ALLER) {
            // 2 - partir le plus tard possible
            $ordre[] = 'tr1.horaireD DESC';
        } else {
            // 2 - arriver le plus tôt possible
            $ordre[] = sprintf('tr%d.horaireA', $nb_troncons);
        }
        // 3 - Départ sur le service de rang le plus bas (ser1.rang)
        $ordre[] = 'tr1.rangService';
        // 4 - Priorité aux points de correspondance repérés
        for ($tr_num = 1; $tr_num < $nb_troncons; $tr_num ++) {
            $ordre[] = sprintf('tr%d.correspondance DESC', $tr_num);
        }
        // 5 - Station desservant l’établissement de rang le plus bas (etasta.rang)
        $ordre[] = 'etasta.rang';
        // 6 - Correspondances de rang le plus bas
        for ($tr_num = 2; $tr_num <= $nb_troncons; $tr_num ++) {
            $ordre[] = sprintf('tr%d.rangService', $tr_num);
        }
        // 7 - Durée de trajet la plus petite (pour les boucles)
        for ($i = 1; $i <= $nb_troncons; $i ++) {
            $ordre[] = sprintf('tr%d.montee_passage DESC', $i);
            $ordre[] = sprintf('tr%d.descente_passage', $i);
        }
        return $ordre;
    }
}