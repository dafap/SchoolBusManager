<?php
/**
 * Description du fichier
 *
 * @project sbm
 * @package
 * @filesource ItineraireInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Arlysere\Itineraire;

interface ItineraireInterface
{

    const ALLER = 1;

    const RETOUR = 2;

    const TOLERANCE_ALLER = 7;

    const TOLERANCE_RETOUR = 15;

    public function setEleveId(int $eleveId);

    public function setEtablissementId(string $etablissementId);

    public function setJours($jours);

    public function setMoment(int $moment);

    public function setNiveau(int $niveau);

    public function setRegimeId(int $regimeId);

    public function setResponsableId(int $responsableId);

    public function setStationId(int $stationId);

    public function setTrajet($trajet);

    public function run();
}