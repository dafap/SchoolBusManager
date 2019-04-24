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
 * @date 28 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\NatureCarte as NatureCarteStrategy;
use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;

class Services extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'services';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Services';
        $this->id_name = 'serviceId';
        $this->strategies['horaire1'] = new SemaineStrategy();
        $this->strategies['horaire2'] = new SemaineStrategy();
        $this->strategies['horaire3'] = new SemaineStrategy();
        $this->strategies['natureCarte'] = new NatureCarteStrategy();
        $tLibelles = $this->db_manager->get('Sbm\Db\System\Libelles');
        $resultset = $tLibelles->fetchAll([
            'nature' => 'NatureCartes'
        ]);
        foreach ($resultset as $row) {
            $this->strategies['natureCarte']->addNatureCarte($row->libelle);
        }
    }

    public function getNatureCartes()
    {
        return $this->strategies['natureCarte']->getNatureCartes();
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

    /**
     * Renvoi un tableau des 3 horaires
     *
     * @param string $serviceId
     *
     * @return array
     */
    public function getHoraires(string $serviceId)
    {
        $oservice = $this->getRecord($serviceId);
        return [
            'horaire1' => $oservice->horaire1,
            'horaire2' => $oservice->horaire2,
            'horaire3' => $oservice->horaire3
        ];
    }
}

