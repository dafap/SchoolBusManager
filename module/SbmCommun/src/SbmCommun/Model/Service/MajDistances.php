<?php
/**
 * Service de mise à jour des distances pour tous les enfants d'un responsable
 *
 * @project sbm
 * @package SbmCommun/Model/Service
 * @filesource MajDistances.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 avr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use DafapSession\Model\Session;
use SbmCartographie\Model\Point;

class MajDistances implements FactoryInterface
{

    /**
     * Service manager
     *
     * @var ServiceLocatorInterface
     */
    private $sm;

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
        $this->sm = $serviceLocator;
        $this->millesime = Session::get('millesime');
        $this->famille = array(
            'enfants' => array(
                1 => array(),
                2 => array()
            ),
            'etablissements' => array()
        );
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
        $responsable = $this->sm->get('Sbm\Db\Table\Responsables')->getRecord($responsableId);
        $this->domicile = new Point($responsable->x, $responsable->y);
        
        // liste des élèves et des établissements à prendre en compte
        $destinations = array();
        for ($i = 1; $i <= 2; $i ++) {
            $rowset = $this->sm->get('Sbm\Db\Query\ElevesScolarites')->getEnfants($responsableId, $i);
            foreach ($rowset as $row) {
                $this->famille['enfants'][$i][$row['eleveId']] = $row['etablissementId'];
                if (array_key_exists($row['etablissementId'], $this->famille['etablissements']))
                    continue;
                $this->famille['etablissements'][$row['etablissementId']] = array(
                    'pt' => new Point($row['xeta'], $row['yeta']),
                    'distance' => 0.0
                );
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
            $tScolarites = $this->sm->get('Sbm\Db\Table\Scolarites');
            $oData = $tScolarites->getObjData();
            for ($i = 1; $i <= 2; $i ++) {
                foreach ($this->famille['enfants'][$i] as $eleveId => $etablissementId) {
                    $oData->exchangeArray(array(
                        'millesime' => $this->millesime,
                        'eleveId' => $eleveId,
                        'distanceR' . $i => round($this->famille['etablissements'][$etablissementId]['distance'] / 1000, 1)
                    ));
                    $tScolarites->saveRecord($oData);
                }
            }
        }
    }
}