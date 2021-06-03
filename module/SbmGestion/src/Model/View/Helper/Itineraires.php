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
 * @date 3 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmGestion\Model\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use SbmBase\Model\StdLib;

class Itineraires extends AbstractHelper implements FactoryInterface
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
	<td class="first semaine" style="white-space: nowrap;">%s</td>
	<td class="next second moment">%s</td>
	<td class="next third ligne">%s</td>
	<td class="next fourth station" title="%s">%s</td>
	<td class="next fifth horaire">%s</td>
	<td class="next sixth station" title="%s">%s</td>
	<td class="next seventh horaire">%s</td>
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
     * @param bool $btn_traite
     *            indique s'il faut afficher la case à cocher 'Traitée' ou non
     * @param int $millesime
     * @return string
     */
    public function __invoke(int $eleveId, int $trajet = 1, bool $btn_traite = false,
        int $millesime = null): string
    {
        $resultset = $this->db_manager->get('Sbm/Db/Query/AffectationsServicesStations')->getItineraires(
            $eleveId, $trajet, $millesime);
        $tr_content = [];
        $nb_itineraires = $resultset->count();
        if ($btn_traite) {
            $td = self::TD . "\n" . '<td class="next eighth traite" rowspan="%d">%%s</td>';
        } else {
            $td = self::TD;
        }
        // élimination des passages multiples en ne gardant que le dernier dans l'ordre du resultset
        $array = [];
        foreach ($resultset as $value) {
            $array[$value['moment']][$value['jours']][$value['correspondance']] = $value;
        }
        foreach ($array as $arrayJours) {
            foreach ($arrayJours as $arrayCorrespondances) {
                foreach ($arrayCorrespondances as $value) {
                    $tr_content[] = self::TR .
                        sprintf($td, $value['jours'], $this->moment($value['moment']),
                            $value['ligne1Id'], $value['commune1'], $value['station1'],
                            $value['horaire1'], $value['commune2'], $value['station2'],
                            $value['horaire2'], $nb_itineraires) . self::END_TR;
                    $td = self::TD;
                }
            }
        }
        return sprintf('<table class="itineraires">%s</table>', implode("\n", $tr_content));
    }

    private function moment(int $value): string
    {
        return StdLib::getParam($value, $this->getMoment(), '');
    }
}