<?php
/**
 * Description du contenu de la page d'accueil pendant la campagne d'inscription
 *
 * Ce fichier est associé à
 * - index-pendant.inc.html qui décrit le format de sprintf
 * - index-pendant.help.php qui décrit les paramètre disponibles
 *
 * Il dispose des propriétés suivantes :
 * 'form' : objet SbmFront\Form\Login
 * 'communes' : objet SbmCommun\Model\Db\Service\Table\Communes'),
 * 'calendar' : objet SbmCommun\Model\Db\Service\Table\System\Calendar
 * 'theme' : objet SbmInstallation\Model\Theme
 * 'client' : tableau décrivant l'organisateur (array)
 * 'accueil' : url du site de l'organisateur
 * 'millesime' : (int)
 * 'as' : année scolaire (string)
 * 'dateDebutAs' : date de début de l'année scolaire (string)
 * 'url_ts_region' : url du site d'inscription de la région (string)
 *
 * @project sbm
 * @package config/themes/ccmgc/sbm-front/index
 * @filesource index-avant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 juin 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\DateLib;

$format = file_get_contents(__DIR__ . '/index-avant.inc.html');
$organisateur = implode('<br>',
    [
        sprintf('<a href="%s" target="_blank">%s</a>', $this->accueil, $this->client['name']),
        implode('<br>', $this->client['adresse']),
        sprintf('%s %s', $this->client['code_postal'], $this->client['commune']),
        $this->telephone($this->client['telephone']),
        $this->client['email']
    ]);
$etat = $this->calendar->getEtatDuSite();
$membres = $this->communes->getListeMembre();
$dateDebut = DateLib::formatDateComplete($etat['dateDebut']);
$dateFin = DateLib::formatDateComplete($etat['dateFin']);
$dateEcheance = DateLib::formatDateComplete($etat['echeance']);
return sprintf($format, $this->as, $dateDebut, $dateFin, $dateEcheance, count($membres),
    $this->client['name'], implode(', ', $membres),
    implode('<br>', $this->calendar->getPermanences()), $organisateur,
    $this->url_ts_region, $this->accueil, $this->url_ts_organisateur);