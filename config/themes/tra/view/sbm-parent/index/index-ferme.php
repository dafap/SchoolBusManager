<?php
/**
 * Description du contenu de la page d'accueil pendant la campagne d'inscription
 *
 * Ce fichier est associé à
 * - index-ferme.inc.html qui décrit le format de sprintf
 * - index-ferme.help.php qui décrit les paramètre disponibles
 *
 * Il dispose des propriétés suivantes :
 * 'theme' : objet SbmInstallation\Model\Theme
 * 'namespacectrl' : controle du namespace
 * 'responsable' : objet responsable créé lors du login
 * 'calendar' : objet SbmCommun\Model\Db\Service\Table\System\Calendar
 * 'inscrits' : résultat de la requête Sbm\Db\Query\ElevesScolarites::getElevesInscrits()
 * 'preinscrits' => résultat de la requête Sbm\Db\Query\ElevesScolarites::getElevesPreinscritsOuEnAttente()
 * 'affectations' => résultat de la requête Sbm\Db\Query\AffectationsServicesStations
 * 'resultats' => objet SbmCommun\Model\Paiement\resultats présentant les justificatifs des sommes dues
 * 'paiements' => ensemble des paiements enregistrés pour ce parent durant cette année scolaire
 * 'factures' => ensemble des factures enregistrées pour ce parent durant cette année scolaire
 * 'client' : tableau décrivant l'organisateur (array)
 * 'accueil' : url du site de l'organisateur
 * 'adresse' : tableau de l'adresse du parent
 *
 * @project sbm
 * @package config/themes/arlysere/view/sbm-parent/index
 * @filesource index-ferme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 déc. 2019
 * @version 2019-2.5.4
 */
use SbmBase\Model\Session;

$format = file_get_contents(__DIR__ . '/index-ferme.inc.phtml');
$etat = $this->calendar->getEtatDuSite();
$organisateur = implode('<br>',
    [
        sprintf('<a href="%s">%s</a>', $this->accueil, $this->client['name']),
        implode('<br>', $this->client['adresse']),
        sprintf('%s %s', $this->client['code_postal'], $this->client['commune']),
        $this->telephone($this->client['telephone']),
        $this->client['email']
    ]);
return sprintf($format, Session::get('as')['libelle'], $etat['dateDebut']->format('d/m/Y'),
    $etat['dateFin']->format('d/m/Y'), $etat['echeance']->format('d/m/Y'),
    $this->client['name'], $organisateur, $this->accueil);
