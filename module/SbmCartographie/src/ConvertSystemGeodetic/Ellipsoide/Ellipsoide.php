<?php
/**
 * Classe de calcul des éléments d'une ellipsoïde
 *
 * On doit définir le grand axe a et au choix :
 *  - soit le petit axe b
 *  - soit l'inverse de l'aplatissement
 *
 *  On peut également donner l'excentricité.
 *
 *  Ce qui n'est pas donné sera calculé.
 *
 * @project sbm
 * @package SbmCartographie/ConvertSystemGeodetic/Ellipsoide
 * @filesource Ellipsoide.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\ConvertSystemGeodetic\Ellipsoide;

use SbmCartographie\ConvertSystemGeodetic\Exception;

class Ellipsoide
{

    private $a;

    private $b;

    private $f;

    private $e;

    private $e_carre;

    private $e2;

    private $e2_carre;

    /**
     * Grand axe
     *
     * @return number
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * Petit axe
     *
     * @return number
     */
    public function getB()
    {
        if (empty($this->b)) {
            $this->calculeB();
        }
        return $this->b;
    }

    /**
     * Première excentricité
     *
     * @return number
     */
    public function getE()
    {
        if (empty($this->e)) {
            $this->calculeE();
        }
        return $this->e;
    }

    /**
     * Carré de la première excentricité
     *
     * @return number
     */
    public function getECarre()
    {
        if (empty($this->e_carre)) {
            $this->calculeE();
        }
        return $this->e_carre;
    }

    /**
     * Deuxième excentricité
     *
     * @return number
     */
    public function getE2()
    {
        if (empty($this->e2)) {
            $this->calculeE2();
        }
        return $this->e2;
    }

    /**
     * Carré de la deuxième excentricité
     *
     * @return number
     */
    public function getE2Carre()
    {
        if (empty($this->e2_carre)) {
            $this->calculeE2();
        }
        return $this->e2_carre;
    }

    /**
     * Aplatissement
     *
     * @return number
     */
    public function getF()
    {
        if (empty($this->f)) {
            $this->calculeF();
        }
        return $this->f;
    }

    /**
     * Grand axe
     *
     * @param number $a
     */
    public function setA($a)
    {
        $this->a = $a;
    }

    /**
     * Petit axe
     *
     * @param number $b
     */
    public function setB($b)
    {
        $this->b = $b;
        $this->calculeE();
        $this->calculeF();
    }

    /**
     * Excentricité
     *
     * @param number $e
     */
    public function setE($e)
    {
        $this->e = $e;
        $this->e_carre = $e * $e;
        $this->b = $this->a * sqrt(1 - $this->e_carre);
        $this->calculeF();
    }

    /**
     * Aplatissement
     *
     * @param number $invF
     *            inverse de l'aplatissement
     * @throws \SbmCartographie\ConvertSystemGeodetic\Exception\RangeException
     */
    public function setF($invF)
    {
        if ($invF != 0) {
            $this->f = 1 / $invF;
            $this->calculeB();
            $this->calculeE();
        } else {
            throw new Exception\RangeException(
                __METHOD__ .
                ' - division par zéro. Il faut donner l\'inverse de l\'aplatissement !');
        }
    }

    /**
     * Calcule le petit axe b à partir du grand axe a et de l'aplatissement f
     */
    private function calculeB()
    {
        $this->b = (1 - $this->f) * $this->a;
    }

    /**
     * Calcule l'aplatissement f à partir du grand axe a et du petit axe b
     *
     * @throws \SbmCartographie\ConvertSystemGeodetic\Exception\RangeException
     */
    private function calculeF()
    {
        if ($this->a != 0) {
            $this->f = ($this->a - $this->b) / $this->a;
        } else {
            throw new Exception\RangeException(__METHOD__ . ' - division par zéro !');
        }
    }

    /**
     *
     * @throws \
     *
     * @throws \SbmCartographie\ConvertSystemGeodetic\Exception\RangeException
     */
    private function calculeE()
    {
        if ($this->a != 0) {
            $a2 = $this->a * $this->a;
            $this->e_carre = ($a2 - $this->b * $this->b) / $a2;
            $this->e = sqrt($this->e_carre);
        } else {
            throw new Exception\RangeException(__METHOD__ . ' - division par zéro !');
        }
    }

    /**
     *
     * @throws \SbmCartographie\ConvertSystemGeodetic\Exception\RangeException
     */
    private function calculeE2()
    {
        if (empty($this->b)) {
            $this->calculeB();
        }
        if ($this->b != 0) {
            $b2 = $this->b * $this->b;
            $this->e2_carre = ($this->a * $this->a - $b2) / $b2;
            $this->e2 = sqrt($this->e2_carre);
        } else {
            throw new Exception\RangeException(__METHOD__ . ' - division par zéro !');
        }
    }

    /**
     * Le rayon moyen, noté R1, est égal au tiers de la somme du grand-axe et du
     * demi-petit axe de l'ellipsoïde.
     *
     * @return number
     */
    public function getRayonMoyen()
    {
        return (2 * $this->getA() + $this->getB()) / 3;
    }

    /**
     * Le rayon authalique, noté R2, est le rayon d'une sphère fictive d'aire (surface)
     * égale à celle de l'ellipsoïde considéré.
     *
     * @return number
     */
    public function getRayonAuthalique()
    {
        $a = $this->getA();
        $b = $this->getB();
        $ln = log(($a + sqrt($a ** 2 - $b ** 2)) / b);
        return sqtr(($a ** 2 + $a * ($b ** 2) * $ln / sqrt($a ** 2 - $b ** 2)) / 2);
    }

    /**
     * Le rayon volumétrique, noté R3, est le rayon d'une sphère fictive de volume égal à
     * celui de l'ellipsoïde considéré.
     *
     * @return number
     */
    public function getRayonVolumétrique()
    {
        return pow((($this->getA() ** 2) * $this->getB()), 1 / 3);
    }
}