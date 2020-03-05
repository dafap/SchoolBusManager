<?php
/**
 * Module SbmCommun
 *
 * @project sbm
 * @package module/SbmCommun/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mars 2020
 * @version 2020-2.6.0
 */
use SbmCommun\Form;
use SbmCommun\Model\View\Helper as ViewHelper;
use SbmCommun\Model\Db\ObjectData;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Service\Horaires;
use SbmCommun\Model\Db\Service\Libelles;
use SbmCommun\Model\Db\Service\Query;
use SbmCommun\Model\Db\Service\Select;
use SbmCommun\Model\Db\Service\Table;
use SbmCommun\Model\Db\Service\TableGateway;
use SbmCommun\Model\Mvc\Controller\Plugin\Service as PluginController;
use SbmCommun\Model\Service\CalculDroits;
use SbmCommun\Model\Service\FormManager;
use SbmCommun\Model\Service\MajDistances;

if (! defined('MODULE_PATH')) {
    define('MODULE_PATH', dirname(__DIR__));
    define('ROOT_PATH', dirname(MODULE_PATH));
}
if (! defined('APPL_NAME')) {
    define('APPL_NAME', 'School Bus Manager');
}

return [
    'controller_plugins' => [
        'invokables' => [
            'redirectToOrigin' => PluginController\RedirectBack::class,
            'csvExport' => PluginController\CsvExport::class
        ]
    ],
    'db_manager' => [
        'invokables' => [
            'Sbm\Db\ObjectData\Affectation' => ObjectData\Affectation::class,
            'Sbm\Db\ObjectData\Appel' => ObjectData\Appel::class,
            'Sbm\Db\ObjectData\Circuit' => ObjectData\Circuit::class,
            'Sbm\Db\ObjectData\Classe' => ObjectData\Classe::class,
            'Sbm\Db\ObjectData\Commune' => ObjectData\Commune::class,
            'Sbm\Db\ObjectData\Eleve' => ObjectData\Eleve::class,
            'Sbm\Db\ObjectData\ElevePhoto' => ObjectData\ElevePhoto::class,
            'Sbm\Db\ObjectData\Etablissement' => ObjectData\Etablissement::class,
            'Sbm\Db\ObjectData\EtablissementService' => ObjectData\EtablissementService::class,
            'Sbm\Db\ObjectData\Facture' => ObjectData\Facture::class,
            'Sbm\Db\ObjectData\Ligne' => ObjectData\Ligne::class,
            'Sbm\Db\ObjectData\Lot' => ObjectData\Lot::class,
            'Sbm\Db\ObjectData\Organisme' => ObjectData\Organisme::class,
            'Sbm\Db\ObjectData\Paiement' => ObjectData\Paiement::class,
            'Sbm\Db\ObjectData\Rpi' => ObjectData\Rpi::class,
            'Sbm\Db\ObjectData\RpiClasse' => ObjectData\RpiClasse::class,
            'Sbm\Db\ObjectData\RpiCommune' => ObjectData\RpiCommune::class,
            'Sbm\Db\ObjectData\RpiEtablissement' => ObjectData\RpiEtablissement::class,
            'Sbm\Db\ObjectData\Scolarite' => ObjectData\Scolarite::class,
            'Sbm\Db\ObjectData\SecteurScolaireClgPu' => ObjectData\SecteurScolaireClgPu::class,
            'Sbm\Db\ObjectData\Service' => ObjectData\Service::class,
            'Sbm\Db\ObjectData\SimulationEtablissement' => ObjectData\SimulationEtablissement::class,
            'Sbm\Db\ObjectData\Station' => ObjectData\Station::class,
            'Sbm\Db\ObjectData\Tarif' => ObjectData\Tarif::class,
            'Sbm\Db\ObjectData\Transporteur' => ObjectData\Transporteur::class,
            'Sbm\Db\ObjectData\User' => ObjectData\User::class,
            'Sbm\Db\ObjectData\UserEtablissement' => ObjectData\UserEtablissement::class,
            'Sbm\Db\ObjectData\UserTransporteur' => ObjectData\UserTransporteur::class,
            'Sbm\Db\SysObjectData\Calendar' => ObjectData\Sys\Calendar::class,
            'Sbm\Db\SysObjectData\Document' => ObjectData\Sys\Document::class,
            'Sbm\Db\SysObjectData\DocAffectation' => ObjectData\Sys\DocAffectation::class,
            'Sbm\Db\SysObjectData\DocColumn' => ObjectData\Sys\DocColumn::class,
            'Sbm\Db\SysObjectData\DocField' => ObjectData\Sys\DocField::class,
            'Sbm\Db\SysObjectData\DocLabel' => ObjectData\Sys\DocLabel::class,
            'Sbm\Db\SysObjectData\DocTable' => ObjectData\Sys\DocTable::class,
            'Sbm\Db\SysObjectData\Libelle' => ObjectData\Sys\Libelle::class
        ],
        'factories' => [
            'Sbm\Db\ObjectData\Responsable' => ObjectData\ResponsableFactory::class,

            'Sbm\Db\Table\Affectations' => Table\Affectations::class,
            'Sbm\Db\Table\Appels' => Table\Appels::class,
            'Sbm\Db\Table\Circuits' => Table\Circuits::class,
            'Sbm\Db\Table\Classes' => Table\Classes::class,
            'Sbm\Db\Table\Communes' => Table\Communes::class,
            'Sbm\Db\Table\Eleves' => Table\Eleves::class,
            'Sbm\Db\Table\ElevesPhotos' => Table\ElevesPhotos::class,
            'Sbm\Db\Table\Etablissements' => Table\Etablissements::class,
            'Sbm\Db\Table\EtablissementsServices' => Table\EtablissementsServices::class,
            'Sbm\Db\Table\Factures' => Table\Factures::class,
            'Sbm\Db\Table\Lignes' => Table\Lignes::class,
            'Sbm\Db\Table\Lots' => Table\Lots::class,
            'Sbm\Db\Table\Organismes' => Table\Organismes::class,
            'Sbm\Db\Table\Paiements' => Table\Paiements::class,
            'Sbm\Db\Table\Responsables' => Table\Responsables::class,
            'Sbm\Db\Table\Rpi' => Table\Rpi::class,
            'Sbm\Db\Table\RpiClasses' => Table\RpiClasses::class,
            'Sbm\Db\Table\RpiCommunes' => Table\RpiCommunes::class,
            'Sbm\Db\Table\RpiEtablissements' => Table\RpiEtablissements::class,
            'Sbm\Db\Table\Scolarites' => Table\Scolarites::class,
            'Sbm\Db\Table\SecteursScolairesClgPu' => Table\SecteursScolairesClgPu::class,
            'Sbm\Db\Table\Services' => Table\Services::class,
            'Sbm\Db\Table\SimulationEtablissements' => Table\SimulationEtablissements::class,
            'Sbm\Db\Table\Stations' => Table\Stations::class,
            'Sbm\Db\Table\Tarifs' => Table\Tarifs::class,
            'Sbm\Db\Table\Transporteurs' => Table\Transporteurs::class,
            'Sbm\Db\Table\Users' => Table\Users::class,
            'Sbm\Db\Table\UsersEtablissements' => Table\UsersEtablissements::class,
            'Sbm\Db\Table\UsersTransporteurs' => Table\UsersTransporteurs::class,
            'Sbm\Db\System\Calendar' => Table\Sys\Calendar::class,
            'Sbm\Db\System\Documents' => Table\Sys\Documents::class,
            'Sbm\Db\System\DocAffectations' => Table\Sys\DocAffectations::class,
            'Sbm\Db\System\DocFields' => Table\Sys\DocFields::class,
            'Sbm\Db\System\DocLabels' => Table\Sys\DocLabels::class,
            'Sbm\Db\System\DocTables' => Table\Sys\DocTables::class,
            'Sbm\Db\System\DocTables\Columns' => Table\Sys\DocColumns::class,
            'Sbm\Db\System\Libelles' => Table\Sys\Libelles::class,

            'Sbm\Db\TableGateway\Affectations' => TableGateway\TableGatewayAffectations::class,
            'Sbm\Db\TableGateway\Appels' => TableGateway\TableGatewayAppels::class,
            'Sbm\Db\TableGateway\Circuits' => TableGateway\TableGatewayCircuits::class,
            'Sbm\Db\TableGateway\Classes' => TableGateway\TableGatewayClasses::class,
            'Sbm\Db\TableGateway\Communes' => TableGateway\TableGatewayCommunes::class,
            'Sbm\Db\TableGateway\Eleves' => TableGateway\TableGatewayEleves::class,
            'Sbm\Db\TableGateway\ElevesPhotos' => TableGateway\TableGatewayElevesPhotos::class,
            'Sbm\Db\TableGateway\Etablissements' => TableGateway\TableGatewayEtablissements::class,
            'Sbm\Db\TableGateway\EtablissementsServices' => TableGateway\TableGatewayEtablissementsServices::class,
            'Sbm\Db\TableGateway\Factures' => TableGateway\TableGatewayFactures::class,
            'Sbm\Db\TableGateway\Lignes' => TableGateway\TableGatewayLignes::class,
            'Sbm\Db\TableGateway\Lots' => TableGateway\TableGatewayLots::class,
            'Sbm\Db\TableGateway\Organismes' => TableGateway\TableGatewayOrganismes::class,
            'Sbm\Db\TableGateway\Paiements' => TableGateway\TableGatewayPaiements::class,
            'Sbm\Db\TableGateway\Responsables' => TableGateway\TableGatewayResponsables::class,
            'Sbm\Db\TableGateway\Rpi' => TableGateway\TableGatewayRpi::class,
            'Sbm\Db\TableGateway\RpiClasses' => TableGateway\TableGatewayRpiClasses::class,
            'Sbm\Db\TableGateway\RpiCommunes' => TableGateway\TableGatewayRpiCommunes::class,
            'Sbm\Db\TableGateway\RpiEtablissements' => TableGateway\TableGatewayRpiEtablissements::class,
            'Sbm\Db\TableGateway\Scolarites' => TableGateway\TableGatewayScolarites::class,
            'Sbm\Db\TableGateway\SecteursScolairesClgPu' => TableGateway\TableGatewaySecteursScolairesClgPu::class,
            'Sbm\Db\TableGateway\Services' => TableGateway\TableGatewayServices::class,
            'Sbm\Db\TableGateway\SimulationEtablissements' => TableGateway\TableGatewaySimulationEtablissements::class,
            'Sbm\Db\TableGateway\Stations' => TableGateway\TableGatewayStations::class,
            'Sbm\Db\TableGateway\Tarifs' => TableGateway\TableGatewayTarifs::class,
            'Sbm\Db\TableGateway\Transporteurs' => TableGateway\TableGatewayTransporteurs::class,
            'Sbm\Db\TableGateway\Users' => TableGateway\TableGatewayUsers::class,
            'Sbm\Db\TableGateway\UsersEtablissements' => TableGateway\TableGatewayUsersEtablissements::class,
            'Sbm\Db\TableGateway\UsersTransporteurs' => TableGateway\TableGatewayUsersTransporteurs::class,
            'Sbm\Db\SysTableGateway\Calendar' => TableGateway\Sys\TableGatewayCalendar::class,
            'Sbm\Db\SysTableGateway\Documents' => TableGateway\Sys\TableGatewayDocuments::class,
            'Sbm\Db\SysTableGateway\DocAffectations' => TableGateway\Sys\TableGatewayDocAffectations::class,
            'Sbm\Db\SysTableGateway\DocColumns' => TableGateway\Sys\TableGatewayDocColumns::class,
            'Sbm\Db\SysTableGateway\DocFields' => TableGateway\Sys\TableGatewayDocFields::class,
            'Sbm\Db\SysTableGateway\DocLabels' => TableGateway\Sys\TableGatewayDocLabels::class,
            'Sbm\Db\SysTableGateway\DocTables' => TableGateway\Sys\TableGatewayDocTables::class,
            'Sbm\Db\SysTableGateway\Libelles' => TableGateway\Sys\TableGatewayLibelles::class,

            'Sbm\Db\Vue\Circuits' => Table\Vue\Circuits::class,
            'Sbm\Db\Vue\Classes' => Table\Vue\Classes::class,
            'Sbm\Db\Vue\Etablissements' => Table\Vue\Etablissements::class,
            'Sbm\Db\Vue\Lots' => Table\Vue\Lots::class,
            'Sbm\Db\Vue\Organismes' => Table\Vue\Organismes::class,
            'Sbm\Db\Vue\Paiements' => Table\Vue\Paiements::class,
            'Sbm\Db\Vue\Responsables' => Table\Vue\Responsables::class,
            'Sbm\Db\Vue\Services' => Table\Vue\Services::class,
            'Sbm\Db\Vue\Stations' => Table\Vue\Stations::class,
            'Sbm\Db\Vue\Tarifs' => Table\Vue\Tarifs::class,
            'Sbm\Db\Vue\Transporteurs' => Table\Vue\Transporteurs::class,

            'Sbm\Db\VueGateway\Circuits' => TableGateway\Vue\TableGatewayCircuits::class,
            'Sbm\Db\VueGateway\Classes' => TableGateway\Vue\TableGatewayClasses::class,
            'Sbm\Db\VueGateway\Etablissements' => TableGateway\Vue\TableGatewayEtablissements::class,
            'Sbm\Db\VueGateway\Lots' => TableGateway\Vue\TableGatewayLots::class,
            'Sbm\Db\VueGateway\Organismes' => TableGateway\Vue\TableGatewayOrganismes::class,
            'Sbm\Db\VueGateway\Paiements' => TableGateway\Vue\TableGatewayPaiements::class,
            'Sbm\Db\VueGateway\Responsables' => TableGateway\Vue\TableGatewayResponsables::class,
            'Sbm\Db\VueGateway\Services' => TableGateway\Vue\TableGatewayServices::class,
            'Sbm\Db\VueGateway\Stations' => TableGateway\Vue\TableGatewayStations::class,
            'Sbm\Db\VueGateway\Tarifs' => TableGateway\Vue\TableGatewayTarifs::class,
            'Sbm\Db\VueGateway\Transporteurs' => TableGateway\Vue\TableGatewayTransporteurs::class,

            'Sbm\Db\Select\Bordereaux' => Select\BordereauxForSelect::class,
            'Sbm\Db\Select\Classes' => Select\ClassesForSelect::class,
            'Sbm\Db\Select\Communes' => Select\CommunesForSelect::class,
            'Sbm\Db\Select\DatesCartes' => Select\DatesCartes::class,
            'Sbm\Db\Select\Eleves' => Select\ElevesForSelect::class,
            'Sbm\Db\Select\Etablissements' => Select\EtablissementsForSelect::class,
            'Sbm\Db\Select\History' => Select\HistoryForSelect::class,
            'Sbm\Db\Select\Lignes' => Select\LignesForSelect::class,
            'Sbm\Db\Select\Lots' => Select\LotsForSelect::class,
            'Sbm\Db\Select\Organismes' => Select\Organismes::class,
            'Sbm\Db\Select\Responsables' => Select\Responsables::class,
            'Sbm\Db\Select\Services' => Select\ServicesForSelect::class,
            'Sbm\Db\Select\Stations' => Select\StationsForSelect::class,
            'Sbm\Db\Select\Tarifs' => Select\TarifsForSelect::class,
            'Sbm\Db\Select\Transporteurs' => Select\Transporteurs::class,
            'Sbm\Db\Select\Libelles' => Select\LibellesForSelect::class,
            'Sbm\Libelles' => Libelles::class,
            'Sbm\Horaires' => Horaires::class,

            'Sbm\Db\Query\Circuits' => Query\Circuit\Circuits::class,
            'Sbm\Db\Query\Eleves' => Query\Eleve\Eleves::class,
            'Sbm\Db\Query\ElevesResponsables' => Query\Eleve\ElevesResponsables::class,
            'Sbm\Db\Query\ElevesScolarites' => Query\Eleve\ElevesScolarites::class,
            'Sbm\Db\Query\ElevesDivers' => SbmCommun\Model\Db\Service\Query\Eleve\Divers::class,
            'Sbm\Db\Query\AffectationsServicesStations' => Query\Eleve\AffectationsServicesStations::class,
            'Sbm\Db\Query\Responsables' => Query\Responsable\Responsables::class,
            'Sbm\Db\Query\Responsable\Montants' => Query\Responsable\CalculMontant::class,
            'Sbm\Db\Query\Responsable\Emails' => Query\Responsable\Emails::class,
            'Sbm\Db\Query\Responsable\Telephones' => Query\Responsable\Telephones::class,
            'Sbm\Db\Query\Etablissements' => Query\Etablissement\Etablissements::class,
            'Sbm\Db\Query\EtablissementsServices' => SbmCommun\Model\Db\Service\Query\Etablissement\EtablissementsServices::class,
            'Sbm\Db\Query\SecteursScolairesClgPu' => Query\Etablissement\SecteursScolairesClgPu::class,
            'Sbm\Db\Query\Services' => Query\Service\Services::class,
            'Sbm\Db\Query\SimulationEtablissements' => Query\Etablissement\SimulationEtablissements::class,
            'Sbm\Db\Query\Stations' => Query\Station\Stations::class,
            Query\Paiement\Calculs::class => Query\Paiement\Calculs::class,
            Query\Station\VersEtablissement::class => Query\Station\VersEtablissement::class,
            'Sbm\Db\Query\Transporteurs' => Query\Transporteur\Transporteurs::class,
            'Sbm\Db\Query\History' => Query\History\History::class,
            'Sbm\Statistiques\Eleve' => Query\Eleve\Statistiques::class,
            'Sbm\Statistiques\Paiement' => Query\Paiement\Statistiques::class,
            'Sbm\Statistiques\Responsable' => Query\Responsable\Statistiques::class
        ]
    ],
    'form_manager' => [
        'invokables' => [
            Form\Calendar::class => Form\Calendar::class,
            Form\Circuit::class => Form\Circuit::class,
            Form\Classe::class => Form\Classe::class,
            Form\Commune::class => Form\Commune::class,
            Form\Etablissement::class => Form\Etablissement::class,
            Form\Ligne::class => Form\Ligne::class,
            Form\Lot::class => Form\Lot::class,
            Form\Organisme::class => Form\Organisme::class,
            Form\Rpi::class => Form\Rpi::class,
            Form\SecteurScolaire::class => Form\SecteurScolaire::class,
            Form\Service::class => Form\Service::class,
            Form\SimulationEtablissement::class => Form\SimulationEtablissement::class,
            Form\Station::class => Form\Station::class,
            Form\Tarif::class => Form\Tarif::class,
            Form\Transporteur::class => Form\Transporteur::class
        ],
        'factories' => [
            Form\Responsable::class => Form\ResponsableFactory::class,
            Form\ResponsableVerrouille::class => Form\ResponsableVerrouille::class
        ]
    ],
    'cartographie_manager' => [
        'factories' => [
            'Sbm\CalculDroitsTransport' => CalculDroits::class,
            'Sbm\MajDistances' => MajDistances::class
        ]
    ],
    'service_manager' => [
        'factories' => [
            'Sbm\DbManager' => DbManager::class,
            'Sbm\FormManager' => FormManager::class
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'affectations' => ViewHelper\Affectations::class,
            'formRowDate' => ViewHelper\FormRowDate::class,
            'formRowDateTime' => ViewHelper\FormRowDateTime::class,
            'iconBarres' =>ViewHelper\Iconbarres::class,
            'listeLigneActions' => ViewHelper\ListeLigneActions::class,
            'ligneMenuAction' => ViewHelper\LigneMenuAction::class,
            'listeZoneActions' => ViewHelper\ListeZoneActions::class,
            'pictogrammes' => ViewHelper\Pictogrammes::class,
            'renderCheckbox' => ViewHelper\RenderCheckbox::class,
            'telephone' => ViewHelper\Telephone::class,
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'sbm/pagination' => __DIR__ . '/../view/partial/pagination.phtml',
            'sbm/mdpchange' => __DIR__ . '/../view/partial/mdpchange.phtml'
        ]
    ]
];
