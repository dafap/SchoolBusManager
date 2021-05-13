<?php
/**
 * Liste des projections et de leurs zones
 *
 * @project sbm
 * @package SbmCartographie/src/ConvertSystemGeodetic/Projection
 * @filesource SelectOptions.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

abstract class SelectOptions
{

    /**
     * renvoie un tableau où les index sont les codes EPGS de la projection
     *
     * @return array
     */
    public static function getOptions()
    {
        return [
            4326 => 'WGS84',
            2154 => 'Lambert 93',
            3942 => 'RGF 93 CC 42',
            3943 => 'RGF 93 CC 43',
            3944 => 'RGF 93 CC 44',
            3945 => 'RGF 93 CC 45',
            3946 => 'RGF 93 CC 46',
            3947 => 'RGF 93 CC 47',
            3948 => 'RGF 93 CC 48',
            3949 => 'RGF 93 CC 49',
            3950 => 'RGF 93 CC 50',
            27561 => 'Lambert I Zone',
            27562 => 'Lambert II Zone',
            27563 => 'Lambert III Zone',
            27564 => 'Lambert IV Zone',
            27571 => 'Lambert I Carto',
            27572 => 'Lambert II Carto',
            27573 => 'Lambert III Carto',
            27574 => 'Lambert IV Carto'
        ];
    }

    /**
     * Renvoie la projection associée à un code EPGS
     *
     * @param int $epgs
     * @return ProjectionInterface
     */
    public static function getProjection(int $epgs): ProjectionInterface
    {
        switch ($epgs) {
            case 4326:
                return new Wgs84();
            case 2154:
                return new Lambert93();
            case 3942:
                return new Lambert06CC9zones(42);
            case 3943:
                return new Lambert06CC9zones(43);
            case 3944:
                return new Lambert06CC9zones(44);
            case 3945:
                return new Lambert06CC9zones(45);
            case 3946:
                return new Lambert06CC9zones(46);
            case 3947:
                return new Lambert06CC9zones(47);
            case 3948:
                return new Lambert06CC9zones(48);
            case 3949:
                return new Lambert06CC9zones(49);
            case 3950:
                return new Lambert06CC9zones(50);
            case 27561:
                return new LambertNTF4zones(1);
            case 27562:
                return new LambertNTF4zones(2);
            case 27563:
                return new LambertNTF4zones(3);
            case 27564:
                return new LambertNTF4zones(4);
            case 27571:
                return new LambertZoneCarto(1);
            case 27572:
                return new LambertZoneCarto(2);
            case 27573:
                return new LambertZoneCarto(3);
            case 27574:
                return new LambertZoneCarto(4);
            default:
                throw new \SbmCartographie\ConvertSystemGeodetic\Exception\DomainException(
                    'La projection demandée n\'est pas implémentée.');
        }
    }
}