<?php
/**
 * Objet parent d'une projection
 *
 * Cette classe abstraite représente une projection avec les méthodes générales de
 * conversion et de calculs.
 * La classe dérivée devra initialiser l'ellipsoide ainsi que les données générales de la
 * projection,
 * puis appeler l'une des méthodes suivantes :
 * - la méthode alg0019 si c'est une projection Lambert conique conforme dans le cas
 * tangent
 * - la méthode alg0054 si c'est une projection Lambert conique conforme dans le cas
 * sécant
 *
 * Les latitudes, longitudes et SbmCartographie\Model\Point sont tous en radian, en entrée
 * comme en sortie
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Projection
 * @filesource AbstractProjection.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Projection;

use SbmCartographie\ConvertSystemGeodetic\Exception;
use SbmCartographie\ConvertSystemGeodetic\Ellipsoide\Ellipsoide;
use SbmCartographie\Model\Point;

abstract class AbstractProjection
{

    /**
     * Ellipsoide sur laquelle s'appuie la projection
     *
     * @var Ellipsoide
     */
    protected $ellipsoide;

    /**
     *
     * @var string 'degré', 'grade', 'radian'
     */
    protected $unit;

    /**
     * Direction des axes
     *
     * @var array
     */
    protected $axe = [
        'longitude' => 'est',
        'latitude' => 'nord'
    ];

    /**
     * Nom de la projection
     *
     * @var string
     */
    protected $name;

    protected $epsg;

    /**
     * Facteur d’échelle à l’origine
     *
     * @var float
     */
    protected $k0;

    /**
     * Latitude origine en unités indiquées
     *
     * @var float
     */
    protected $latitude_of_origin;

    /**
     * Longitude en unités indiquées du centre de la projection par rapport au méridien
     * origine (Greenwich)
     *
     * @var float
     */
    protected $central_meridian;

    /**
     * Latitude en unités indiquées du 1er parallèle automécoïque
     *
     * @var float
     */
    protected $standard_parallel_1;

    /**
     * Latitude en unités indiquées du 2ème parallèle automécoïque
     *
     * @var float
     */
    protected $standard_parallel_2;

    /**
     * Abscisse en projection du point origine (appelée aussi False Easting)
     *
     * @var float
     */
    protected $x0;

    /**
     * Ordonnée en projection du point origine (appelée aussi False Northing)
     *
     * @var float
     */
    protected $y0;

    /**
     * longitude origine en radian par rapport au méridien origine
     *
     * @var float
     */
    protected $lambda_0;

    /**
     * Latitude origine en radian
     *
     * @var float
     */
    protected $phi_0;

    /**
     * Latitude en radian du 1er parallèle automécoïque
     *
     * @var float
     */
    protected $phi_1;

    /**
     * Latitude en radian du 2ème parallèle automécoïque
     *
     * @var float
     */
    protected $phi_2;

    /**
     * longitude origine en radian par rapport au méridien origine
     *
     * @var float
     */
    protected $lambda_c;

    /**
     * Exposant de la projection
     *
     * @var float
     */
    protected $n;

    /**
     * Constante de projection
     *
     * @var float
     */
    protected $C;

    /**
     * Abscisse du pole de la projection
     *
     * @var float
     */
    protected $Xs;

    /**
     * Ordonnée du pole de la projection
     *
     * @var float
     */
    protected $Ys;

    /**
     * Les 7 paramètres de conversion
     *
     * @var array
     */
    protected $paramsToWgs84 = [
        0,
        0,
        0,
        0,
        0,
        0,
        0
    ];

    /**
     * Position du méridien d'origine par rapport au méridien de Greenwich (exprimé en
     * degrés décimaux)
     *
     * @var float
     */
    protected $primem = 0;

    /**
     * Place l'ellipsoide sur laquelle porte les calculs de la projection
     *
     * @param Ellipsoide $e
     */
    protected function setEllipsoide(Ellipsoide $e)
    {
        $this->ellipsoide = $e;
    }

    public function getEllipsoide(): Ellipsoide
    {
        return $this->ellipsoide;
    }

    /**
     * Demi grand axe
     *
     * @return float
     */
    public function getA()
    {
        return $this->ellipsoide->getA();
    }

    /**
     * Demi petit axe
     *
     * @return float
     */
    public function getB()
    {
        return $this->ellipsoide->getB();
    }

    /**
     * Donne la constante de projection
     *
     * @return float
     */
    public function getC()
    {
        return $this->C;
    }

    /**
     * Première excentricité
     *
     * @return float
     */
    public function getE()
    {
        return $this->ellipsoide->getE();
    }

    /**
     * Deuxième excentricité
     *
     * @return float
     */
    public function getE2()
    {
        return $this->ellipsoide->getE2();
    }

    /**
     * Carré de la deuxième excentricité
     *
     * @return float
     */
    public function getE2Carre()
    {
        return $this->ellipsoide->getE2Carre();
    }

    /**
     * Carré de la première excentricité
     *
     * @return float
     */
    public function getECarre()
    {
        return $this->ellipsoide->getECarre();
    }

    /**
     * Aplatissement
     *
     * @return float
     */
    public function getF()
    {
        return $this->ellipsoide->getF();
    }

    /**
     * Donne le facteur d’échelle à l’origine
     *
     * @return float
     */
    public function getK0()
    {
        return $this->k0;
    }

    /**
     * Donne la longitude origine en radian par rapport au méridien origine
     *
     * @return float
     */
    public function getLambdaC()
    {
        return $this->lambda_c;
    }

    /**
     * Donne l'exposant de la projection
     *
     * @return float
     */
    public function getN()
    {
        return $this->n;
    }

    /**
     * Donne le nom de la projection
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Donne l'une des propriétés phi_0, phi_1, phi_2 selon $n
     *
     * @param int $n
     *            $n prend ses valeurs dans [0-2]
     */
    public function getPhi($n)
    {
        $prp = 'phi_' . $n;
        return $this->{$prp};
    }

    /**
     * Donne l'abscisse en projection du pole
     *
     * @return float
     */
    public function getXs()
    {
        return $this->Xs;
    }

    /**
     * Donne l'ordonnée en projection du pole
     *
     * @return float
     */
    public function getYs()
    {
        return $this->Ys;
    }

    /**
     * Initialise, si nécessaire, les propriétés lambda_0, phi_0, phi_1 et phi_2 en
     * radians en tenant compte de l'unité de la projection.
     *
     * @throws \SbmCartographie\ConvertSystemGeodetic\Exception\DomainException
     */
    private function initUniteDGR()
    {
        $prp = [
            'central_meridian' => 'lambda_0',
            'latitude_of_origin' => 'phi_0',
            'standard_parallel_1' => 'phi_1',
            'standard_parallel_2' => 'phi_2'
        ];
        foreach ($prp as $vu => $vr) {
            if (! isset($this->{$vu}))
                continue;
            switch ($this->unit) {
                case 'degré':
                    $this->{$vr} = $this->{$vu} * pi() / 180;
                    break;
                case 'grade':
                    $this->{$vr} = $this->{$vu} * pi() / 200;
                    break;
                case 'radian':
                    $this->{$vr} = $this->{$vu};
                    break;
                default:
                    throw new Exception\DomainException(
                        __METHOD__ .
                        ' - Unité non conforme. On attend degré, grade ou radian.');
                    break;
            }
        }
        // primem est toujours exprimé en degré
        if (isset($this->primem)) {
            $this->lambda_0 += $this->primem * pi() / 180;
        }
    }

    /**
     * Calcul de la latitude isométrique sur un ellipsoïde de première excentricité e au
     * point de latitude $phi
     *
     * @param float $phi
     *            $phi est en radians
     * @return float
     */
    public function alg0001($phi)
    {
        $sin = sin($phi);
        $e_sin = $this->getE() * $sin;
        $base = (1 - $e_sin) / (1 + $e_sin);
        return log(tan(pi() / 4 + $phi / 2) * pow($base, $this->getE() / 2));
    }

    /**
     * Calcul de la latitude phi (exprimée en radians) à partir de la latitude isométrique
     * $li
     *
     * @param float $li
     *            latitude isométrique
     * @param float $eps
     *            tolérance de convergence
     * @return float
     */
    public function alg0002($li, $eps = 1e-11)
    {
        $phi_0 = 2 * atan(exp($li)) - pi() / 2;
        $phi = $this->calculPourAlg0002sp1($li, $phi_0);
        $delta = abs($phi - $phi_0);
        while ($delta > $eps) {
            $delta = $phi;
            $phi = $this->calculPourAlg0002sp1($li, $phi);
            $delta = abs($phi - $delta);
        }
        return $phi;
    }

    /**
     * Calcul intermédiaire pour la méthode alg0002
     *
     * @param float $li
     *            latitude isométrique
     * @param float $phi
     *            en radians
     * @return float
     */
    private function calculPourAlg0002sp1($li, $phi)
    {
        $e_sin = $this->getE() * sin($phi);
        $base = (1 + $e_sin) / (1 - $e_sin);
        $x = pow($base, $this->getE() / 2) * exp($li);
        return 2 * atan($x) - pi() / 2;
    }

    /**
     * Transformation de coordonnées géographiques en coordonnées en projection conique
     * conforme de Lambert
     *
     * @param float $lambda
     *            en radians
     * @param float $phi
     *            en radians
     * @return \SbmCartographie\Model\Point
     */
    public function alg0003($lambda, $phi)
    {
        $li = $this->alg0001($phi);
        $c_exp_n_li = $this->getC() * exp(- $this->getN() * $li);
        $n_lambda_lambdaC = $this->getN() * ($lambda - $this->getLambdaC());
        return new Point($this->getXs() + $c_exp_n_li * sin($n_lambda_lambdaC),
            $this->getYs() - $c_exp_n_li * cos($n_lambda_lambdaC));
    }

    /**
     * Transformation de coordonnées en projection conique conforme de Lambert, en
     * coordonnées géographiques
     *
     * @param float $x
     * @param float $y
     *
     * @return \SbmCartographie\Model\Point
     */
    public function alg0004($x, $y)
    {
        $x1 = $x - $this->getXs();
        $y1 = $this->getYs() - $y;
        $R = sqrt($x1 * $x1 + $y1 * $y1);
        $gama = atan2($x1, $y1);
        $li = - log(abs($R / $this->getC())) / $this->getN();
        return new Point($this->getLambdaC() + $gama / $this->getN(), $this->alg0002($li),
            0, 'radian');
    }

    /**
     * Transformation de coordonnées géographiques ellipsoïdales en coordonnées
     * cartésiennes
     *
     * @param float $lambda
     *            longitude
     * @param float $phi
     *            latitude
     * @param float $he
     *            élévation
     * @return \SbmCartographie\Model\Point
     */
    public function alg0009($lambda, $phi, $he = 0)
    {
        $grdNormale = $this->alg0021($phi);
        return new Point(($grdNormale + $he) * cos($phi) * cos($lambda),
            ($grdNormale + $he) * cos($phi) * sin($lambda),
            ($grdNormale * (1 - $this->getECarre()) + $he) * sin($phi));
    }

    /**
     * Transformation, pour un ellipsoïde donné, des coordonnées cartésiennes d’un point
     * en coordonnées géographiques ellipsoïdales par la méthode de
     * Heiskanen-Moritz-Boucher.
     *
     * @param float $x
     * @param float $y
     * @param float $z
     *
     * @return \SbmCartographie\Model\Point
     */
    public function alg0012($x, $y, $z = 0, $eps = 1e-11)
    {
        $lambda = atan2($y, $x);
        $module_xy = sqrt($x * $x + $y * $y);
        $phi = atan2($z,
            $module_xy *
            (1 - $this->getA() * $this->getECarre() / sqrt($x * $x + $y * $y + $z * $z)));
        $delta = 1 + $eps;
        while ($delta > $eps) {
            $phi_1 = $phi;
            $tmp = sin($phi_1);
            $tmp = sqrt(1 - $this->getECarre() * $tmp * $tmp); // racine(1 - e²
                                                               // sin²($phi_1))
            $phi = atan2($z * $tmp,
                $module_xy * $tmp - $this->getA() * $this->getECarre() * cos($phi_1));
            $delta = abs($phi - $phi_1);
        }
        $he = $module_xy / cos($phi) -
            $this->getA() / sqrt(1 - $this->getECarre() * sin($phi) * sin($phi));
        return new Point($lambda, $phi, $he, 'radian');
    }

    /**
     * Transformation 7 paramètres pour un point donné en coordonnées cartésiennes
     *
     * @param Point $point
     *            point sur lequel porte la transformation
     * @param number $tx
     *            translation sur x
     * @param number $ty
     *            translation sur y
     * @param number $tz
     *            translation sur y
     * @param number $k
     *            facteur d'échelle
     * @param number $rx
     *            rotation sur x
     * @param number $ry
     *            la rotation sur y
     * @param number $rz
     *            rotation sur z
     * @return \SbmCartographie\Model\Point
     */
    public function alg0013(Point $point, $tx = 0, $ty = 0, $tz = 0, $k = 0, $rx = 0,
        $ry = 0, $rz = 0)
    {
        return $point->translate($tx, $ty, $tz);
        // ->ajoute($point->dilate(1 + $k))
        // ->ajoute($point->rotate($rx, $ry, $rz));
    }

    /**
     * Détermination des paramètres de calcul d’une projection Lambert conique conforme
     * dans le cas tangent, avec ou sans facteur d'échelle en fonction des paramètres de
     * définition usuels
     * Utilise les propriétés k0 et phi_0 de l'objet (ainsi que lambda_0, x0 et y0).
     * Calcule les propriétés lambda_c, n, C, Xs et Ys.
     *
     * @throws \SbmCartographie\ConvertSystemGeodetic\Exception\OutOfRangeException
     */
    public function alg0019()
    {
        $this->initUniteDGR();
        if (! isset($this->k0) || ! isset($this->phi_0) || ! isset($this->lambda_0) ||
            ! isset($this->x0) || ! isset($this->y0)) {
            ob_start();
            var_dump($this);
            $dump_obj = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception\OutOfRangeException(
                __METHOD__ .
                " - Cette projection ne définit pas les constantes nécessaires à une projection Lambert conique conforme dans le cas tangent.\n$dump_obj");
        }
        $this->lambda_c = $this->lambda_0;
        $this->n = sin($this->phi_0);
        $dy = $this->k0 * $this->alg0021($this->phi_0) / tan($this->phi_0);
        $this->C = $dy * exp($this->n * $this->alg0001($this->phi_0));
        $this->Xs = $this->x0;
        $this->Ys = $this->y0 + $dy;
    }

    /**
     * Calcul de la grande normale de l’ellipsoïde
     *
     * @param float $phi
     * @return float
     */
    public function alg0021($phi)
    {
        $sin = sin($phi);
        return $this->getA() / sqrt(1 - $this->getECarre() * $sin * $sin);
    }

    /**
     * Détermination des paramètres de calcul d'une projection Lambert conique conforme
     * dans le cas sécant.
     * Utilise les propriétés phi_0, phi_1 et phi_2 de l'objet (ainsi que lambda_0, x0 et
     * y0).
     * Calcule les propriétés lambda_c, n, C, Xs et Ys.
     *
     * @throws \SbmCartographie\ConvertSystemGeodetic\Exception\OutOfRangeException
     */
    public function alg0054()
    {
        $this->initUniteDGR();

        if (! isset($this->phi_0) || ! isset($this->phi_1) || ! isset($this->phi_2) ||
            ! isset($this->lambda_0) || ! isset($this->x0) || ! isset($this->y0)) {
            ob_start();
            var_dump($this);
            $dump_obj = html_entity_decode(strip_tags(ob_get_clean()));
            throw new Exception\OutOfRangeException(
                __METHOD__ .
                " - Cette projection ne définit pas les constantes nécessaires à une projection Lambert conique conforme dans le cas sécant.\n$dump_obj");
        }
        $this->lambda_c = $this->lambda_0;
        $n1cos1 = $this->alg0021($this->phi_1) * cos($this->phi_1);
        $n2cos2 = $this->alg0021($this->phi_2) * cos($this->phi_2);
        $li1 = $this->alg0001($this->phi_1);
        $li2 = $this->alg0001($this->phi_2);
        $this->n = log($n2cos2 / $n1cos1) / ($li1 - $li2);
        $this->C = $n1cos1 / $this->n * exp($this->n * $li1);
        $this->Xs = $this->x0;
        if (abs($this->phi_0 - pi() / 2) < 1e-13) {
            $this->Ys = $this->y0;
        } else {
            $this->Ys = $this->y0 +
                $this->C * exp(- $this->n * $this->alg0001($this->phi_0));
        }
    }
}