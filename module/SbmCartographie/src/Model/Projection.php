<?php
/**
 * Projection configurée dans le fichier module.config.php
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package SbmCartographie/Model
 * @filesource Projection.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCartographie\Model;

use SbmBase\Model\StdLib;
use SbmCartographie\ConvertSystemGeodetic\Projection\AbstractProjection;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;

class Projection extends AbstractProjection implements ProjectionInterface
{

    /**
     *
     * @var \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    private $projection = null;

    private $rangeLat = [];

    private $rangeLng = [];

    private $rangeX = [];

    private $rangeY = [];

    /**
     *
     * @param \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface $projection
     * @param array $config_cartes
     */
    public function __construct($projection, $config_cartes)
    {
        $this->projection = $projection;
        foreach ([
            'etablissement',
            'station',
            'gestion',
            'parent'
        ] as $nature) {
            $rangeValid = StdLib::getParamR([
                $nature,
                'valide'
            ], $config_cartes);
            $this->rangeLat[$nature] = $rangeValid['lat'];
            $this->rangeLng[$nature] = $rangeValid['lng'];

            $pt = new Point();
            $pt->setLatitude($rangeValid['lat'][0]);
            $pt->setLongitude($rangeValid['lng'][0]);
            $pt = $this->gRGF93versXYZ($pt);
            $this->rangeX[$nature][0] = $pt->getX();
            $this->rangeY[$nature][0] = $pt->getY();

            $pt = new Point();
            $pt->setLatitude($rangeValid['lat'][1]);
            $pt->setLongitude($rangeValid['lng'][1]);
            $pt = $this->gRGF93versXYZ($pt);
            $this->rangeX[$nature][1] = $pt->getX();
            $this->rangeY[$nature][1] = $pt->getY();
        }
    }

    /**
     * Reçoit un point en coordonnées géographiques (RGF93 ou WPS84) exprimées en degré et renvoie
     * un point en coordonnées xyz
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
     * Reçoit un point en coordonnées xyz et renvoie un point en coordonnées géographiques (RGF93
     * ou WPS84) exprimées en degré
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
     *            'parent', 'gestion', 'station' ou 'etablissement'
     *            
     * @throws \SbmCartographie\Model\Exception\DomainException
     *
     * @return boolean
     */
    public function isValid(Point $p, $nature = 'parent')
    {
        if (! in_array($nature, [
            'etablissement',
            'station',
            'gestion',
            'parent'
        ])) {
            throw new Exception\DomainException(__METHOD__ . ' - Paramètre `nature` non conforme.');
        }
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