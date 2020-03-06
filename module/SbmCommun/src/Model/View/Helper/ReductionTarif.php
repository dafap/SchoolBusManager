<?php
/**
 * Traduit la colonne reduction en texte
 * Réduit ou Normal
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/View/Helper
 * @filesource ReductionTarif.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCommun\Model\Paiements\GrilleTarifInterface;

class ReductionTarif extends AbstractHelper implements GrilleTarifInterface, FactoryInterface
{
    private $reduction_tarif;
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->getServiceLocator()->get('Sbm\DbManager');
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $this->reduction_tarif=$tTarifs->getReduits();
        return $this;
    }

    public function __invoke($data)
    {
        if ($data) {
            return $this->reduction_tarif[self::REDUIT];
        } else {
            return $this->reduction_tarif[self::NORMAL];
        }
    }
}
