<?php
/**
 * Projection configurée dans le fier module.config.phpchi
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package SbmCartographie/Model
 * @filesource Projection.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmCartographie\Model;

use SbmCartographie\ConvertSystemGeodetic\Projection\AbstractProjection;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmBase\Model\StdLib;

class Projection extends AbstractProjection implements ProjectionInterface
{

    /**
     *
     * @var SbmCartographie\Projection\ProjectionInterface
     */
    private $projection = null;

    private $rangeLat = array();

    private $rangeLng = array();

    private $rangeX = array();

    private $rangeY = array();

    /**
     *
     * @param \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface $projection            
     * @param array $config_cartes            
     */
    public function __construct($projection, $config_cartes)
    {
        $this->projection = $projection;        
        $rangeEtab = StdLib::getParamR(array(
            'etablissements',
            'valide'
        ), $config_cartes);
        $rangeParent = StdLib::getParamR(array(
            'parent',
            'valide'
        ), $config_cartes);
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
        return $p->setLatLngRange($this->rangeLat[$nature], $this->rangeLng[$nature])
            ->setXYRange($this->rangeX[$nature], $this->rangeY[$nature])
            ->isValid();
    }

    public function getRangeLat()
    {
        return $this->rangeLat;
    }

    public function getRangeLng()
    {
        return $this->rangeLng;
    }

    public function getRangeX()
    {
        return $this->rangeX;
    }

    public function getRangeY()
    {
        return $this->rangeY;
    }
}