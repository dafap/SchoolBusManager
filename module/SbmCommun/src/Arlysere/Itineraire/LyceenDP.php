<?php
/**
 * Recherche d'itinéraires pour un lycéen
 *
 * Le mercredi soir est un moment codé SPECIAL + 3. Il est nécessairement placé en fin du
 * tableau constant MOMENT[].
 * La méthode getMoment() renvoie le moment codé de 1 à 3 (matin, midi, soir)
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Itineraire
 * @filesource LyceenDP.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Arlysere\Itineraire;

use SbmCommun\Arlysere\Exception;
use Zend\Db\Sql\Where;

class LyceenDP extends AbstractItineraire
{

    // du lundi au vendredi
    const JOURS_MATIN = 31;

    // le mercredi
    const JOURS_MERCREDI = 4;

    // du lu-ma-je-ve (Le mercredi soir est cherché à part)
    const JOURS_SOIR = 27;

    // base pour moment spécial. On ajoute 1, 2, 3 ou 4 selon le moment désiré
    const SPECIAL = 400;

    // moments pour lesquels on doit rechercher un itinéraire
    // le moment du mercredi soir doit être en fin de tableau
    protected const MOMENTS = [
        1,
        2,
        3,
        self::SPECIAL + 3
    ];

    private const MOMENTFAUX = " \nCe moment (%d) n\'est pas compatible avec un lycéen DP";

    public function run()
    {
        $this->process(self::MOMENTS);
    }

    protected function init()
    {
        parent::init();
        $this->setNiveau(8)->setRegimeId(0);
    }

    protected function getWhereTroncon(): Where
    {
        $where = parent::getWhereTroncon();
        return $where->equalTo('lig.internes', $this->regimeId);
    }

    protected function prepareItineraire(int $nb_troncons)
    {
        parent::prepareItineraire($nb_troncons);
        $ref = self::MOMENTS;
        switch ($this->moment) {
            case 1:
                $this->select->where($this->getConditionsMatin($nb_troncons));
                break;
            case 2:
                $this->select->where($this->getConditionsMidi($nb_troncons));
                break;
            case 3:
            case end($ref):
                $this->select->where($this->getConditionsSoir($nb_troncons));
                break;
            default:
                throw new Exception\OutOfBoundsException(
                    __METHOD__ . sprintf(self::MOMENTFAUX), $this->moment);
                break;
        }
        return $this;
    }

    /**
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
            $where->literal(sprintf('tr%d.internes = 0', $num_tr))->literal(
                sprintf('(tr%d.semaine & %d) <> 0', $num_tr, $this->jours));
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
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::getFiltreJours()
     */
    protected function getFiltreJours(int $moment = 0): int
    {
        $ref = self::MOMENTS;
        switch ($moment ?: $this->moment) {
            case 1:
                return self::JOURS_MATIN;
                break;
            case 3:
                return self::JOURS_SOIR;
                break;
            case 2:
            case end($ref):
                return self::JOURS_MERCREDI;
                break;
            default:
                throw new Exception\OutOfBoundsException(
                    __METHOD__ . sprintf(self::MOMENTFAUX), $this->moment);
                break;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \SbmCommun\Arlysere\Itineraire\AbstractItineraire::getMoment()
     */
    protected function getMoment(int $moment = 0): int
    {
        return ($moment ?: $this->moment) % self::SPECIAL;
    }
}