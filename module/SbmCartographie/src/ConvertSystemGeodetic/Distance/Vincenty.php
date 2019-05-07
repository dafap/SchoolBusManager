<?php
/**
 * Calcul de distance par approximation sur un ellipsoïde
 *
 * Usage :
 * $oDistance = new Vincenty();
 * $distance = $oDistance->setProjection($p)->getDistance($p1, $p2);
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Distance
 * @filesource Vincenty.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Distance;

use SbmCartographie\Model\Point;

class Vincenty extends AbstractDistance implements DistanceInterface
{

    /**
     * Calcul de distance par la méthode itérative de Vincenty
     *
     * {@inheritdoc}
     * @see \SbmCartographie\ConvertSystemGeodetic\Distance\DistanceInterface::getDistance()
     */
    public function getDistance(Point $point1, Point $point2): float
    {
        $point1 = $this->pointUniteEnRadians($point1);
        $point2 = $this->pointUniteEnRadians($point2);

        $a = $this->projection->getEllipsoide()->getA();
        $b = $this->projection->getEllipsoide()->getB();
        $f = $this->projection->getEllipsoide()->getF();

        $lat1 = $point1->getLatitude('radian');
        $lat2 = $point2->getLatitude('radian');

        $lon1 = $point1->getLongitude('radian');
        $lon2 = $point2->getLongitude('radian');

        $L = $lon2 - $lon1;
        $U1 = atan((1 - $f) * tan($lat1));
        $U2 = atan((1 - $f) * tan($lat2));

        $iterationLimit = 100;
        $lambda = $L;
        $sinU1 = sin($U1);
        $sinU2 = sin($U2);
        $cosU1 = cos($U1);
        $cosU2 = cos($U2);

        do {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSigma = sqrt(
                ($cosU2 * $sinLambda) * ($cosU2 * $sinLambda) +
                ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) *
                ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda));
            if (abs($sinSigma) < 1E-12) {
                return 0.0;
            }
            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cosSqAlpha = 1 - $sinAlpha * $sinAlpha;
            $cos2SigmaM = 0;
            if (abs($cosSqAlpha) > 1E-12) {
                $cos2SigmaM = $cosSigma - 2 * $sinU1 * $sinU2 / $cosSqAlpha;
            }
            $C = $f / 16 * $cosSqAlpha * (4 + $f * (4 - 3 * $cosSqAlpha));
            $lambdaP = $lambda;
            $lambda = $L +
                (1 - $C) * $f * $sinAlpha *
                ($sigma +
                $C * $sinSigma *
                ($cos2SigmaM + $C * $cosSigma * (- 1 + 2 * $cos2SigmaM * $cos2SigmaM)));
        } while (abs($lambda - $lambdaP) > 1e-12 && -- $iterationLimit > 0);
        if ($iterationLimit === 0) {
            throw new \Exception('Vincenty calculation does not converge');
        }
        $uSq = $cosSqAlpha * ($a * $a - $b * $b) / ($b * $b);
        $A = 1 + $uSq / 16384 * (4096 + $uSq * (- 768 + $uSq * (320 - 175 * $uSq)));
        $B = $uSq / 1024 * (256 + $uSq * (- 128 + $uSq * (74 - 47 * $uSq)));
        $deltaSigma = $B * $sinSigma *
            ($cos2SigmaM +
            $B / 4 *
            ($cosSigma * (- 1 + 2 * $cos2SigmaM * $cos2SigmaM) -
            $B / 6 * $cos2SigmaM * (- 3 + 4 * $sinSigma * $sinSigma) *
            (- 3 + 4 * $cos2SigmaM * $cos2SigmaM)));
        $s = $b * $A * ($sigma - $deltaSigma);
        return round($s, 3);
    }
}