<?php
/**
 * Lambert zone 1 à 4 Carto
 *
 * @project sbm
 * @package SbmCartographie/src/ConvertSystemGeodetic/Projection
 * @filesource LambertZoneCarto.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

class LambertZoneCarto extends LambertNTF4zones
{
    public function __construct($nzone)
    {
        parent::__construct($nzone);
        switch ($nzone) {
            case 1:
                $this->y0 = 1200000.0;

                break;
            case 2:
                $this->y0 = 2200000.0;
                break;
            case 3:
                $this->y0 = 3200000.0;
                break;
            case 4:
                $this->y0 = 4185861.369;
                break;
        }
        $this->alg0019();
    }
}