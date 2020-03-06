<?php
/**
 * Traduit la colonne duplicata en texte
 * Duplicata ou Abonnement
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/View/Helper
 * @filesource NatureGrilleTarif.php
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

class NatureGrilleTarif extends AbstractHelper implements GrilleTarifInterface, FactoryInterface
{
    private $nature_grille;
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->getServiceLocator()->get('Sbm\DbManager');
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $this->nature_grille=$tTarifs->getDuplicatas();
        return $this;
    }

    public function __invoke($data)
    {
        if ($data) {
            return $this->nature_grille[self::DUPLICATA];
        } else {
            return $this->nature_grille[self::ABONNEMENT];
        }
    }
}
