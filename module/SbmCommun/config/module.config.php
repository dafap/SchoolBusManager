<?php
/**
 * Module SbmCommun
 *
 * @project sbm
 * @package module/SbmCommun/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept. 2014
 * @version 2014-1
 */
if (! defined('MODULE_PATH')) {
    define('MODULE_PATH', dirname(__DIR__));
    define('ROOT_PATH', dirname(MODULE_PATH));
}
if (! defined('APPL_NAME')) {
    define('APPL_NAME', 'School Bus Manager');
}
return array(
    'service_manager' => array(
        'invokables' => array(
            'SbmCommun\FlashMessenger' => 'SbmCommun\Listener\FlashMessengerListener',
            'Sbm\Db\ObjectData\Circuit' => 'SbmCommun\Model\Db\ObjectData\Circuit',
            'Sbm\Db\ObjectData\Classe' => 'SbmCommun\Model\Db\ObjectData\Classe',
            'Sbm\Db\ObjectData\Commune' => 'SbmCommun\Model\Db\ObjectData\Commune',
            'Sbm\Db\ObjectData\Eleve' => 'SbmCommun\Model\Db\ObjectData\Eleve',
            'Sbm\Db\ObjectData\Etablissement' => 'SbmCommun\Model\Db\ObjectData\Etablissement',
            'Sbm\Db\ObjectData\Paiement' => 'SbmCommun\Model\Db\ObjectData\Paiement',
            'Sbm\Db\ObjectData\Responsable' => 'SbmCommun\Model\Db\ObjectData\Responsable',
            'Sbm\Db\ObjectData\Scolarite' => 'SbmCommun\Model\Db\ObjectData\Scolarite',
            'Sbm\Db\ObjectData\Service' => 'SbmCommun\Model\Db\ObjectData\Service',
            'Sbm\Db\ObjectData\Station' => 'SbmCommun\Model\Db\ObjectData\Station',
            'Sbm\Db\ObjectData\Tarif' => 'SbmCommun\Model\Db\ObjectData\Tarif',
            'Sbm\Db\ObjectData\Transporteur' => 'SbmCommun\Model\Db\ObjectData\Transporteur',
            'Sbm\Db\SysObjectData\Calendar' => 'SbmCommun\Model\Db\ObjectData\Sys\Calendar',
            'Sbm\Db\SysObjectData\Document' => 'SbmCommun\Model\Db\ObjectData\Sys\Document',
            'Sbm\Db\SysObjectData\DocAffectation' => 'SbmCommun\Model\Db\ObjectData\Sys\DocAffectation',
            'Sbm\Db\SysObjectData\DocColumn' => 'SbmCommun\Model\Db\ObjectData\Sys\DocColumn',
            'Sbm\Db\SysObjectData\DocField' => 'SbmCommun\Model\Db\ObjectData\Sys\DocField',
            'Sbm\Db\SysObjectData\DocTable' => 'SbmCommun\Model\Db\ObjectData\Sys\DocTable',
            'Sbm\Db\SysObjectData\Libelle' => 'SbmCommun\Model\Db\ObjectData\Sys\Libelle'
        ),
        'factories' => array(
            'Sbm\Db\DbLib' => 'SbmCommun\Model\Db\Service\DbLibService',
            'Sbm\Db\Table\Circuits' => 'SbmCommun\Model\Db\Service\Table\Circuits',
            'Sbm\Db\Table\Classes' => 'SbmCommun\Model\Db\Service\Table\Classes',
            'Sbm\Db\Table\Communes' => 'SbmCommun\Model\Db\Service\Table\Communes',
            'Sbm\Db\Table\Eleves' => 'SbmCommun\Model\Db\Service\Table\Eleves',
            'Sbm\Db\Table\Etablissements' => 'SbmCommun\Model\Db\Service\Table\Etablissements',
            'Sbm\Db\Table\Paiements' => 'SbmCommun\Model\Db\Service\Table\Paiements',
            'Sbm\Db\Table\Responsables' => 'SbmCommun\Model\Db\Service\Table\Responsables',
            'Sbm\Db\Table\Scolarites' => 'SbmCommun\Model\Db\Service\Table\Scolarites',
            'Sbm\Db\Table\Services' => 'SbmCommun\Model\Db\Service\Table\Services',
            'Sbm\Db\Table\Stations' => 'SbmCommun\Model\Db\Service\Table\Stations',
            'Sbm\Db\Table\Tarifs' => 'SbmCommun\Model\Db\Service\Table\Tarifs',
            'Sbm\Db\Table\Transporteurs' => 'SbmCommun\Model\Db\Service\Table\Transporteurs',
            'Sbm\Db\System\Calendar' => 'SbmCommun\Model\Db\Service\Table\Sys\Calendar',
            'Sbm\Db\System\Documents' => 'SbmCommun\Model\Db\Service\Table\Sys\Documents',
            'Sbm\Db\System\DocAffectations' => 'SbmCommun\Model\Db\Service\Table\Sys\DocAffectations',
            'Sbm\Db\System\DocFields' => 'SbmCommun\Model\Db\Service\Table\Sys\DocFields',
            'Sbm\Db\System\DocTables' => 'SbmCommun\Model\Db\Service\Table\Sys\DocTables',
            'Sbm\Db\System\DocTables\Columns' => 'SbmCommun\Model\Db\Service\Table\Sys\DocColumns',
            'Sbm\Db\System\Libelles' => 'SbmCommun\Model\Db\Service\Table\Sys\Libelles',
            
            'Sbm\Db\TableGateway\Circuits' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayCircuits',
            'Sbm\Db\TableGateway\Classes' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayClasses',
            'Sbm\Db\TableGateway\Communes' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayCommunes',
            'Sbm\Db\TableGateway\Eleves' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayEleves',
            'Sbm\Db\TableGateway\Etablissements' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayEtablissements',
            'Sbm\Db\TableGateway\Paiements' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayPaiements',
            'Sbm\Db\TableGateway\Responsables' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayResponsables',
            'Sbm\Db\TableGateway\Scolarites' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayScolarites',
            'Sbm\Db\TableGateway\Services' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayServices',
            'Sbm\Db\TableGateway\Stations' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayStations',
            'Sbm\Db\TableGateway\Tarifs' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayTarifs',
            'Sbm\Db\TableGateway\Transporteurs' => 'SbmCommun\Model\Db\Service\TableGateway\TableGatewayTransporteurs',
            'Sbm\Db\SysTableGateway\Calendar' => 'SbmCommun\Model\Db\Service\TableGateway\Sys\TableGatewayCalendar',
            'Sbm\Db\SysTableGateway\Documents' => 'SbmCommun\Model\Db\Service\TableGateway\Sys\TableGatewayDocuments',
            'Sbm\Db\SysTableGateway\DocAffectations' => 'SbmCommun\Model\Db\Service\TableGateway\Sys\TableGatewayDocAffectations',
            'Sbm\Db\SysTableGateway\DocColumns' => 'SbmCommun\Model\Db\Service\TableGateway\Sys\TableGatewayDocColumns',
            'Sbm\Db\SysTableGateway\DocFields' => 'SbmCommun\Model\Db\Service\TableGateway\Sys\TableGatewayDocFields',
            'Sbm\Db\SysTableGateway\DocTables' => 'SbmCommun\Model\Db\Service\TableGateway\Sys\TableGatewayDocTables',
            'Sbm\Db\SysTableGateway\Libelles' => 'SbmCommun\Model\Db\Service\TableGateway\Sys\TableGatewayLibelles',
            
            'Sbm\Db\Vue\Circuits' => 'SbmCommun\Model\Db\Service\Table\Vue\Circuits',
            'Sbm\Db\Vue\Eleves' => 'SbmCommun\Model\Db\Service\Table\Vue\Eleves',
            'Sbm\Db\Vue\Etablissements' => 'SbmCommun\Model\Db\Service\Table\Vue\Etablissements',
            'Sbm\Db\Vue\Paiements' => 'SbmCommun\Model\Db\Service\Table\Vue\Paiements',
            'Sbm\Db\Vue\Responsables' => 'SbmCommun\Model\Db\Service\Table\Vue\Responsables',
            'Sbm\Db\Vue\Services' => 'SbmCommun\Model\Db\Service\Table\Vue\Services',
            'Sbm\Db\Vue\Stations' => 'SbmCommun\Model\Db\Service\Table\Vue\Stations',
            'Sbm\Db\Vue\Transporteurs' => 'SbmCommun\Model\Db\Service\Table\Vue\Transporteurs',
            
            'Sbm\Db\VueGateway\Circuits' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayCircuits',
            'Sbm\Db\VueGateway\Eleves' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayEleves',
            'Sbm\Db\VueGateway\Etablissements' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayEtablissements',
            'Sbm\Db\VueGateway\Paiements' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayPaiements',
            'Sbm\Db\VueGateway\Responsables' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayResponsables',
            'Sbm\Db\VueGateway\Services' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayServices',
            'Sbm\Db\VueGateway\Stations' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayStations',
            'Sbm\Db\VueGateway\Transporteurs' => 'SbmCommun\Model\Db\Service\TableGateway\Vue\TableGatewayTransporteurs',
            
            // 'Sbm\Db\Select\Calendar' => 'SbmCommun\Model\Db\Service\Select\Calendar',
            // 'Sbm\Db\Select\CommunesMembres' => 'SbmCommun\Model\Db\Service\Select\CommunesMembres',
            'Sbm\Db\Select\CommunesDesservies' => 'SbmCommun\Model\Db\Service\Select\CommunesDesservies',
            'Sbm\Db\Select\EtablissementsVisibles' => 'SbmCommun\Model\Db\Service\Select\EtablissementsVisibles',
            'Sbm\Db\Select\Responsables' => 'SbmCommun\Model\Db\Service\Select\Responsables',
            // 'Sbm\Db\Select\Services' => 'SbmCommun\Model\Db\Service\Select\Services',
            // 'Sbm\Db\Select\StationsVisibles' => 'SbmCommun\Model\Db\Service\Select\StationsVisibles',
            'Sbm\Db\Select\Transporteurs' => 'SbmCommun\Model\Db\Service\Select\Transporteurs',
            'Sbm\Libelles\Caisse' => 'SbmCommun\Model\Db\Service\Select\LibellesCaisse',
            'Sbm\Libelles\ModeDePaiement' => 'SbmCommun\Model\Db\Service\Select\LibellesModeDePaiement',
            
            'Sbm\Libelles' => '\SbmCommun\Model\Db\Service\Libelles'
        )
    ),
    'view_manager' => array(
        'template_map' => array(
            'sbm/pagination' => __DIR__ . '/../view/partial/pagination.phtml'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'listeLigneActions' => 'SbmCommun\Form\View\Helper\ListeLigneActions',
            'listeZoneActions' => 'SbmCommun\Form\View\Helper\ListeZoneActions',
        )
    )
);