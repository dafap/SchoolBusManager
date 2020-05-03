<?php
/**
 * Gestion de la table `etablissements-stations`
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/Service/Table
 * @filesource EtablissementsStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class EtablissementsStations extends AbstractSbmTable
{
    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'etablissements-stations';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\EtablissementsStations';
        $this->id_name = [
            'etablissementId',
            'stationId'
        ];
    }
}

