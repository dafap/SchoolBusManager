<?php
/**
 * Gestion de la table `affectations`
 *
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Affectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Expression;

class Affectations extends AbstractSbmTable
{

    /**
     * Initialisation du circuit
     */
    protected function init()
    {
        $this->table_name = 'affectations';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Affectations';
        $this->id_name = array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'correspondance'
        );
    }
}