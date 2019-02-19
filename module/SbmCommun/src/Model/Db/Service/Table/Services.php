<?php
/**
 * Gestion de la table `services`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\NatureCarte as NatureCarteStrategy;

class Services extends AbstractSbmTable
{

    /**
     *
     * @var NatureCarteStrategy
     */
    private $strategy;

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'services';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Services';
        $this->id_name = 'serviceId';
        $this->strategy = new NatureCarteStrategy();
        $tLibelles = $this->db_manager->get('Sbm\Db\System\Libelles');
        $resultset = $tLibelles->fetchAll([
            'nature' => 'NatureCartes'
        ]);
        foreach ($resultset as $row) {
            $this->strategy->addNatureCarte($row->libelle);
        }
    }

    protected function setStrategies()
    {
        $this->hydrator->addStrategy('natureCarte', $this->strategy);
    }

    public function getNatureCartes()
    {
        return $this->strategy->getNatureCartes();
    }

    public function setSelection($serviceId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'serviceId' => $serviceId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }
}

