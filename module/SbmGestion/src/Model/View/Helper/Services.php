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
 * @date 2 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\View\Helper;

use SbmBase\Model\Session;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;

class Services extends AbstractHelper implements FactoryInterface
{
    use \SbmCommun\Model\Traits\ServiceTrait;

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
     * @param int $millesime
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
        $content = [];
        foreach ($resultset as $affectation) {
            $service1Id = $this->getDesignation($affectation, 1);
            $content[$service1Id] = $service1Id;
            $ligne2Id = $affectation->ligne2Id;
            if (! empty($ligne2Id)) {
                $service2Id = $this->getDesignation($affectation, 2);
                $content[$service2Id] = $service2Id;
            }
        }
        return implode('<br>', $content);
    }

    private function getDesignation(ObjectDataInterface $objectdata, int $n)
    {
        $data = [
            'ligneId' => $objectdata->{'ligne' . $n . 'Id'},
            'sens' => $objectdata->{'sensligne' . $n},
            'moment' => $objectdata->moment,
            'ordre' => $objectdata->{'ordreligne' . $n}
        ];
        return $this->identifiantService($data);
    }
}