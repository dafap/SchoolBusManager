<?php
/**
 * Aide de vue permettant d'afficher les services d'un élève dans la liste des élèves
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmGestion/Model/View/Helper
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2018
 * @version 2018-2.4.1
 */
namespace SbmGestion\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;

class Services extends AbstractHelper implements FactoryInterface
{

    protected $db_manager;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->getServiceLocator()->get('Sbm\DbManager');
        return $this;
    }

    /**
     * Renvoie le texte à afficher dans la colonne Services de la liste d'élèves
     *
     * @param int $eleveId            
     * @param int $trajet
     *            1 ou 2
     *            
     * @return string
     */
    public function __invoke($eleveId, $trajet, $millesime = null)
    {
        if (is_null($millesime)) {
            $millesime = Session::get('millesime');
        }
        $where = new Where();
        $where->equalTo('millesime', $millesime)
            ->equalTo('eleveId', $eleveId)
            ->equalTo('trajet', $trajet);
        $resultset = $this->db_manager->get('Sbm\Db\Table\Affectations')->fetchAll($where);
        $content = array();
        foreach ($resultset as $affectation) {
            $service1Id = $affectation->service1Id;
            $content[$service1Id] = $service1Id;
            $service2Id = $affectation->service2Id;
            if (! empty($service2Id)) {
                $content[$service2Id] = $service2Id;
            }
        }
        return implode('<br>', $content);
    }
}