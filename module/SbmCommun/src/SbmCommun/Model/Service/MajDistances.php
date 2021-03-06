<?php
/**
 * Service de mise à jour des distances pour tous les enfants d'un responsable
 *
 * @project sbm
 * @package SbmCommun/Model/Service
 * @filesource MajDistances.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmCommun\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;
use SbmCartographie\Model\Point;
use SbmCartographie\Model\Service\CartographieManager;
use SbmCartographie\Model\Exception;

class MajDistances implements FactoryInterface
{

    /**
     * Service manager
     *
     * @var ServiceLocatorInterface
     */
    private $db_manager;

    /**
     * millesime sur lequel on travaille
     *
     * @var int
     */
    private $millesime;

    /**
     * structure de données de la classe
     *
     * @var array
     */
    private $famille;

    /**
     * Point origine du calcul des distances
     *
     * @var \SbmCartographie\Model\Point
     */
    private $domicile;

    /**
     *
     * @var \SbmCartographie\GoogleMaps\DistanceEtablissements
     */
    private $oDomicileEtablissements;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator->has('SbmCarto\DistanceEtablissements')) {
            throw new Exception(sprintf('CartographieManager attendu.'));
        }
        $this->db_manager = $serviceLocator->get('Sbm\DbManager');
        $this->millesime = Session::get('millesime');
        $this->famille = [
            'enfants' => [
                1 => [],
                2 => []
            ],
            'etablissements' => []
        ];
        $this->oDomicileEtablissements = $serviceLocator->get('SbmCarto\DistanceEtablissements');
        return $this;
    }

    /**
     * Met à jour les distances pour tous les enfants du responsable indiqué.
     * L'enregistrement est fait dans la table scolarites, pour le millesime en cours.
     * (Un seul appel à l'API de google)
     *
     * @param int $responsableId            
     */
    public function pour($responsableId)
    {
        // domicile
        $responsable = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord($responsableId);
        $this->domicile = new Point($responsable->x, $responsable->y);
        
        // liste des élèves et des établissements à prendre en compte
        $destinations = [];
        for ($i = 1; $i <= 2; $i ++) {
            $rowset = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEnfants($responsableId, $i);
            foreach ($rowset as $row) {
                $this->famille['enfants'][$i][$row['eleveId']] = $row['etablissementId'];
                if (array_key_exists($row['etablissementId'], $this->famille['etablissements']))
                    continue;
                $this->famille['etablissements'][$row['etablissementId']] = [
                    'pt' => new Point($row['xeta'], $row['yeta']),
                    'distance' => 0.0
                ];
                $destinations[] = $this->famille['etablissements'][$row['etablissementId']]['pt'];
            }
        }
        if (! empty($destinations)) {
            // appel de l'API
            $result = $this->oDomicileEtablissements->uneOriginePlusieursDestinations($this->domicile, $destinations);
            
            // analyse du résultat. On n'a qu'un domicile donc qu'une distance par établissement. Cette distance est en mètres.
            $j = 0;
            foreach ($this->famille['etablissements'] as $etablissementId => &$array) {
                $array['distance'] = $result[$j ++];
            }
            
            // maj table scolarites (conversion des distances en km)
            $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $oData = $tScolarites->getObjData();
            for ($i = 1; $i <= 2; $i ++) {
                foreach ($this->famille['enfants'][$i] as $eleveId => $etablissementId) {
                    $oData->exchangeArray([
                        'millesime' => $this->millesime,
                        'eleveId' => $eleveId,
                        'distanceR' . $i => round($this->famille['etablissements'][$etablissementId]['distance'] / 1000, 1)
                    ]);
                    $tScolarites->saveRecord($oData);
                }
            }
        }
    }
}