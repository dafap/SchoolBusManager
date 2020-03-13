<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project project_name
 * @package package_name
 * @filesource ChoixGrille.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 mars 2020
 * @version 2020-1
 */
namespace SbmCommun\Arlysere\Tarification;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;


class ChoixGrille implements FactoryInterface
{

    /**
     * Table tarif
     *
     * @var \SbmCommun\Model\Db\Service\Table\Tarifs
     */
    private $tTarifs;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        return $this;
    }


}