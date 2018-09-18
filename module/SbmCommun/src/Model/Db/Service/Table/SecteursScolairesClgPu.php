<?php
/**
 * Gestion de la table `secteurs-scolaires-clg-pu`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource SecteursScolairesClgPu.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Service\Table;

class SecteursScolairesClgPu extends AbstractSbmTable
{

    /**
     * Initialisation du circuit
     */
    protected function init()
    {
        $this->table_name = 'secteurs-scolaires-clg-pu';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\SecteursScolairesClgPu';
        $this->id_name = [
            'communeId',
            'etablissementId'
        ];
    }
}