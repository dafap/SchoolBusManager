<?php
/**
 * Projection configurée dans le fier module.config.phpchi
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmCartographie/Model
 * @filesource Projection.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 juin 2015
 * @version 2015-1
 */
namespace SbmCartographie\Model;

use SbmCartographie\ConvertSystemGeodetic\Projection\AbstractProjection;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCommun\Model\StdLib;

class Projection extends AbstractProjection implements ProjectionInterface, ServiceLocatorAwareInterface
{

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $sm;

    /**
     *
     * @var SbmCartographie\Projection\ProjectionInterface
     */
    private $projection = null;

    private $rangeLat = array();

    private $rangeLng = array();

    private $rangeX = array();

    private $rangeY = array();

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->sm;
    }

    private function init()
    {
        if (is_null($this->projection)) {
            $ns = '\\' . explode('\\', __NAMESPACE__)[0] . '\\ConvertSystemGeodetic\\Projection\\';
            $config = $this->getServiceLocator()->get('Config');
            $system = $ns . StdLib::getParamR(array(
                'cartographie',
                'system'
            ), $config);
            $nzone = StdLib::getParamR(array(
                'cartographie',
                'nzone'
            ), $config, 0);
            $this->projection = new $system($nzone);
            
            $rangeEtab = StdLib::getParamR(array(
                'sbm',
                'cartes',
                'etablissements',
                'valide'
            ), $config);
            $rangeParent = StdLib::getParamR(array(
                'sbm',
                'cartes',
                'parent',
                'valide'
            ), $config);
            // configure le rectangle de validité pour les etablissements et les stations
            $this->rangeLat['etablissements'] = $rangeEtab['lat'];
            $this->rangeLng['etablissements'] = $rangeEtab['lng'];
            $pt = new Point();
            $pt->setLatitude($rangeEtab['lat'][0]);
            $pt->setLongitude($rangeEtab['lng'][0]);
            $pt = $this->gRGF93versXYZ($pt);
            $this->rangeX['etablissements'][0] = $pt->getX();
            $this->rangeY['etablissements'][0] = $pt->getY();
            unset($pt);
            $pt = new Point();
            $pt->setLatitude($rangeEtab['lat'][1]);
            $pt->setLongitude($rangeEtab['lng'][1]);
            $pt = $this->gRGF93versXYZ($pt);
            $this->rangeX['etablissements'][1] = $pt->getX();
            $this->rangeY['etablissements'][1] = $pt->getY();
            unset($pt);
            // configure le rectangle de validité pour les parents et les élèves
            $this->rangeLat['parent'] = $rangeEtab['lat'];
            $this->rangeLng['parent'] = $rangeEtab['lng'];
            $pt = new Point();
            $pt->setLatitude($rangeParent['lat'][0]);
            $pt->setLongitude($rangeParent['lng'][0]);
            $pt = $this->gRGF93versXYZ($pt);
            $this->rangeX['parent'][0] = $pt->getX();
            $this->rangeY['parent'][0] = $pt->getY();
            unset($pt);
            $pt = new Point();
            $pt->setLatitude($rangeEtab['lat'][1]);
            $pt->setLongitude($rangeEtab['lng'][1]);
            $pt = $this->gRGF93versXYZ($pt);
            $this->rangeX['parent'][1] = $pt->getX();
            $this->rangeY['parent'][1] = $pt->getY();
            unset($pt);
        }
    }

    /**
     * Reçoit un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degré et renvoie un point en coordonnées xyz
     *
     * @param Point $p
     *            longitude et latitude exprimées en degré
     *            
     * @return \SbmCartographie\Model\Point
     */
    public function gRGF93versXYZ(Point $p)
    {
        $this->init();
        return $this->projection->gRGF93versXYZ($p);
    }

    /**
     * Reçoit un point en coordonnées xyz et renvoie un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degré
     *
     * @param Point $p            
     * @return \SbmCartographie\Model\Point (les coordonnées du point résulat sont en degré)
     */
    public function xyzVersgRGF93(Point $p)
    {
        $this->init();
        return $this->projection->xyzVersgRGF93($p);
    }

    /**
     * Renvoie la validité d'un point en fonction de sa nature
     *
     * @param Point $p            
     * @param string $nature
     *            'parent' ou 'etablissements'
     *            
     * @return boolean
     */
    public function isValid(Point $p, $nature = 'parent')
    {
        $this->init();
        return $p->setLatLngRange($this->rangeLat[$nature], $this->rangeLng[$nature])
            ->setXYRange($this->rangeX[$nature], $this->rangeY[$nature])
            ->isValid();
    }
}