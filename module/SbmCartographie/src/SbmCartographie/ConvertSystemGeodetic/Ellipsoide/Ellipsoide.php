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
 * @date 27 mars 2015
 * @version 2015-1
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
     * @return real
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * Petit axe
     *
     * @return real
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
     * @return real
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
     * @return real
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
     * @return real;
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
     * @return real;
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
     * @return real
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
     * @param real $a            
     */
    public function setA($a)
    {
        $this->a = $a;
    }

    /**
     * Petit axe
     *
     * @param real $b            
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
     * @param real $e            
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
     * @param real $invF
     *            inverse de l'aplatissement
     */
    public function setF($invF)
    {
        if ($invF != 0) {
            $this->f = 1 / $invF;
            $this->calculeB();
            $this->calculeE();
        } else {
            throw new Exception(__METHOD__ . ' - division par zéro. Il faut donner l\'inverse de l\'aplatissement !');
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
     * @throws Exception
     */
    private function calculeF()
    {
        if ($this->a != 0) {
            $this->f = ($this->a - $this->b) / $this->a;
        } else {
            throw new Exception(__METHOD__ . ' - division par zéro !');
        }
    }
    
    private function calculeE()
    {
        if ($this->a != 0) {
            $a2 = $this->a * $this->a;
            $this->e_carre = ($a2 - $this->b * $this->b) / $a2;
            $this->e = sqrt($this->e_carre);
        } else {
            throw new Exception(__METHOD__ . ' - division par zéro !');
        }
    }
    
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
            throw new Exception(__METHOD__ . ' - division par zéro !');
        }
    } 
}