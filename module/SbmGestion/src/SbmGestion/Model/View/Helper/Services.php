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
 * @date 17 août 2015
 * @version 2015-1
 */
namespace SbmGestion\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use DafapSession\Model\Session;

class Services extends AbstractHelper implements ServiceLocatorAwareInterface
{

    protected $sm;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->sm;
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
    public function __invoke($eleveId, $trajet)
    {
        $millesime = Session::get('millesime');
        $where = new Where();
        $where->equalTo('millesime', $millesime)->equalTo('eleveId', $eleveId)->equalTo('trajet', $trajet);
        $resultset = $this->sm->getServiceLocator()->get('Sbm\Db\Table\Affectations')->fetchAll($where);
        $content = array();
        foreach ($resultset as $affectation) {
            $service1Id = $affectation->service1Id;
            $content[$service1Id] = $service1Id;
            $service2Id = $affectation->service2Id;
            if (!empty($service2Id)) {
                $content[$service2Id] = $service2Id;
            }
        }
        return implode('<br>', $content);
    }
}