<?php
/**
 * Description du contenu de la page d'accueil pendant la campagne d'inscription
 *
 * Ce fichier est associé à
 * - index-pendant.inc.html qui décrit le format de sprintf
 * - index-pendant.help.php qui décrit les paramètre disponibles
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
 * @filesource index-pendant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 avr. 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\Session;

$format = file_get_contents(__DIR__ . '/index-pendant.inc.phtml');
$etat = $this->calendar->getEtatDuSite();
$organisateur = implode('<br>',
    [
        sprintf('<a href="%s">%s</a>', $this->accueil, $this->client['name']),
        implode('<br>', array_filter($this->client['adresse'])),
        sprintf('%s %s', $this->client['code_postal'], $this->client['commune']),
        $this->telephone($this->client['telephone']),
        $this->client['email']
    ]);
$permanences = $this->calendar->getPermanences($this->responsable->commune);
switch (count($permanences)) {
    case 0:
        $permanences = '';
        break;
    case 1:
        $permanences = current($permanences);
        break;
    default:
        $permanences = implode(', ', $permanences);
        break;
}
$url_modif_adresse = $this->url('sbmparentconfig', [
    'action' => 'modif-adresse'
]);
$url_ajoutEleve = $this->url('sbmparent', [
    'action' => 'reinscription-eleve'
]);
$menu = $this->listeZoneActions([],
    [
        'modif-adresse' => [
            'class' => 'accueil default',
            'formaction' => $url_modif_adresse,
            'value' => 'Modifier adresse ou téléphone'
        ],
        'inscrire' => [
            'class' => 'accueil default',
            'formaction' => $url_ajoutEleve,
            'value' => 'Inscrire un enfant'
        ]
    ]);
$dateenvoi = (new \DateTime(Session::get('as')['dateDebut']))->modify('8 days ago')->format(
    'd/m/Y');
return sprintf($format,
    Session::get('as')['libelle'],
    $etat['dateDebut']->format('d/m/Y'),
    $etat['dateFin']->format('d/m/Y'),
    $etat['echeance']->format('d/m/Y'),
    implode('<br>', array_filter($this->adresse)),
    $this->client['name'],
    $menu,
    $permanences,
    $organisateur,
    $this->url_ts_region,
    $this->accueil,
    $this->url_ts_organisateur,
    $dateenvoi);