<?php
/**
 * Aide de vue pour afficher le tableau des itinéraires dans une liste d'élèves
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmGestion/src/Model/View/Helper
 * @filesource Itineraires.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juil. 2020
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
    const TR = '<tr>';

    const END_TR = '</tr>';

    /**
     * Modèle de ligne du tableau des affectations On trouve dans l'ordre : - les jours de
     * passage - le moment de passage - la ligne 1 - la station 1 - l'horaire à la station
     * 1 - la station 2 (descente) - le ligne 2 (correspondance) Les éléments <i> passent
     * - data-trajet qui est un entier (le trajet) - data-href qui est une url composée à
     * l'aide de $args
     *
     * @var string
     */
    const TD = '
	<td class="first">%s</td>
	<td class="next second">%s</td>
	<td class="next third">%s</td>
	<td class="next fourth">%s</td>
	<td class="next fifth">%s</td>
	<td class="next sixth">%s</td>
	<td class="next seventh">%s</td>
';

    protected $db_manager;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->getServiceLocator()->get('Sbm\DbManager');
        return $this;
    }

    /**
     * Renvoie un tableau des itinéraires
     *
     * @param int $eleveId
     * @param int $trajet
     * @param int $millesime
     * @return string
     */
    public function __invoke(int $eleveId, int $trajet = 1, int $millesime = null): string
    {
        $resultset = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations')->getItineraires(
            $eleveId, $trajet, $millesime);
        $tr_content = [];
        foreach ($resultset as $value) {
            $tr_content[] = self::TR . sprintf(self::TD,$value['semaine'],) . self::END_TR;
        }
    }
}