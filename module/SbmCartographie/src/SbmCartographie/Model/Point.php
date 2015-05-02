<?php
/**
 * Définition d'un point et des opérations qui s'appliquent à un point
 *
 * 
 * @project sbm
 * @package SbmCartographie/Model
 * @filesource Point.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2015
 * @version 2015-1
 */
namespace SbmCartographie\Model;

class Point
{

    /**
     * Abscisse ou Longitude
     *
     * @var double
     */
    private $x;

    /**
     * Ordonnée ou Latitude
     *
     * @var double
     */
    private $y;

    /**
     * Cote ou altitude ou elevation ou rayon
     *
     * @var double
     */
    private $z;

    /**
     * Distance en km
     *
     * @var double
     */
    private $distance;

    /**
     * Identifiant de la commune
     *
     * @var string(5)
     */
    private $communeId;

    /**
     * Adresse postale | latlgn
     *
     * @var string
     */
    private $adresse;

    /**
     * Attributs du point
     * 
     * @var array
     */
    private $attributes = array();
    
    /**
     * Unité si nécessaire
     *
     * @var string
     */
    private $unite;

    /**
     * Table des angles plats pour conversion d'unités
     *
     * @var array
     */
    private $plat = array();

    /**
     * Constructeur
     *
     * @param number $x            
     * @param number $y            
     * @param number $z            
     * @param string $unite
     *            vide (par défaut), 'degré', 'grade' ou 'radian'
     */
    public function __construct($x = 0, $y = 0, $z = 0, $unite = '')
    {
        if (! in_array($unite, array(
            '',
            'degré',
            'grade',
            'radian'
        ))) {
            throw new Exception(__CLASS__ . ' - unité incorrecte.');
        }
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->unite = $unite;
        $this->plat = array(
            'degré' => 180,
            'grade' => 200,
            'radian' => pi()
        );
    }

    public function getX()
    {
        return $this->x;
    }

    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    public function getZ()
    {
        return $this->z;
    }

    public function setZ($z)
    {
        $this->z = $z;
        return $this;
    }

    /**
     * Renvoie la distance en km si elle est connue
     *
     * @return double
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Enregistre la distance en km
     *
     * @param float $d
     *            distance à enregistrer (en m ou en km)
     * @param string $unites
     *            unité utilisée
     */
    public function setDistance($d, $unites = 'km')
    {
        switch ($unites) {
            case 'km':
                $this->distance = $d;
                break;
            case 'm':
                $this->distance = $d / 1000;
                break;
        }
    }
    
    /**
     * Affecte une valeur à un attribut
     * 
     * @param string $attribut
     * @param mixed $value
     */
    public function setAttribute($attribut, $value)
    {
        $this->attributes[$attribut] = $value;
    }

    /**
     * Renvoie la valeur de l'attribut ou null s'il n'existe pas
     * 
     * @param string $attribut
     * @return mixed|NULL
     */
    public function getAttribute($attribut)
    {
        if (array_key_exists($attribut, $this->attributes)) {
            return $this->attributes[$attribut];
        } else {
            return null;
        }
    }
    /**
     * Selon que le point est cartésien (XY ou XYZ) ou géographique (longitude, latitude)
     * - pour un point cartésien, unite est vide ''
     * - pour un point géographique, unité est une unité d'angle au singulier (degré, grade, radian)
     *
     * @return string
     */
    public function getUnite()
    {
        return $this->unite;
    }

    /**
     * Renvoie un point obtenu par une translation selon le vecteur fourni.
     *
     * @param real $x            
     * @param real $y            
     * @param number $z            
     *
     * @return \SbmCartographie\Model\Point
     */
    public function translate($x, $y, $z = 0)
    {
        $x = $this->x + $x;
        $y = $this->y + $y;
        $z = $this->z + $z;
        return new Point($x, $y, $z);
    }

    /**
     * Renvoie un point obtenu par une homothétie centrale de rapport donné
     *
     * @param
     *            float k;
     *            
     * @return \SbmCartographie\Model\Point
     */
    public function dilate($k)
    {
        $x = $this->x * $k;
        $y = $this->y * $k;
        $z = $this->z * $k;
        return new Point($x, $y, $z);
    }

    /**
     * Renvoie un point obtenu par une rotation selon le vecteur fourni (en radians)
     *
     * @param float $rx            
     * @param float $ry            
     * @param float $rz            
     */
    public function rotate($rx, $ry, $rz)
    {
        $x = $ry * $this->z - $rz * $this->y;
        $y = $rz * $this->x - $rx * $this->y;
        $z = $rx * $this->y - $ry * $this->x;
        return new Point($x, $y, $z);
    }

    /**
     * C'est une translation mais le paramètre de la méthode est un Point
     *
     * @param Point $p            
     * @return \SbmCartographie\Model\Point
     */
    public function ajoute(Point $p)
    {
        $x = $this->x + $p->x;
        $y = $this->y + $p->y;
        $z = $this->z + $p->z;
        return new Point($x, $y, $z);
    }

    /**
     * Convertit dans l'unité indiquée
     *
     * @param string $unite
     *            'degré' (par défaut), 'grade' ou 'radian'
     * @return \SbmCartographie\Model\Point
     */
    public function to($unite)
    {
        if ($this->unite != '' && $unite != $this->unite) {
            $this->x *= $this->plat[$unite] / $this->plat[$this->unite];
            $this->y *= $this->plat[$unite] / $this->plat[$this->unite];
            $this->unite = $unite;
        }
        return $this;
    }

    /**
     * Donne la longitude
     *
     * @param string $unite
     *            'degré' (par défaut), 'grade' ou 'radian'
     * @throws Exception
     * @return double|number
     */
    public function getLongitude($unite = 'degré')
    {
        if (! in_array($unite, array(
            'degré',
            'grade',
            'radian'
        ))) {
            throw new Exception(__METHOD__ . ' - unité incorrecte.');
        }
        if ($unite == $this->unite) {
            return $this->x;
        } else {
            return $this->x * $this->plat[$unite] / $this->plat[$this->unite];
        }
    }

    /**
     * Donne la latitude
     *
     * @param string $unite
     *            'degré' (par défaut), 'grade' ou 'radian'
     * @throws Exception
     * @return double|number
     */
    public function getLatitude($unite = 'degré')
    {
        if (! in_array($unite, array(
            'degré',
            'grade',
            'radian'
        ))) {
            throw new Exception(__METHOD__ . ' - unité incorrecte.');
        }
        if ($unite == $this->unite) {
            return $this->y;
        } else {
            return $this->y * $this->plat[$unite] / $this->plat[$this->unite];
        }
    }

    /**
     * Fixe la longitude
     *
     * @param real $longitude            
     * @param string $unite
     *            'degré' (par défaut), 'grade' ou 'radian'
     */
    public function setLongitude($longitude, $unite = 'degré')
    {
        if (! in_array($unite, array(
            'degré',
            'grade',
            'radian'
        ))) {
            throw new Exception(__METHOD__ . ' - unité incorrecte.');
        }
        $this->x = $longitude;
        $this->unite = $unite;
    }

    /**
     * Fixe la latitude
     *
     * @param real $latitude            
     * @param string $unite
     *            'degré' (par défaut), 'grade' ou 'radian'
     */
    public function setLatitude($latitude, $unite = 'degré')
    {
        if (! in_array($unite, array(
            'degré',
            'grade',
            'radian'
        ))) {
            throw new Exception(__METHOD__ . ' - unité incorrecte.');
        }
        $this->y = $latitude;
        $this->unite = $unite;
    }
    
    /**
     * Renvoie le point transformé aux coordonnées et unité de $p
     * mais garde tous ses paramètres et attributs.
     * 
     * @param Point $p
     */
    public function transforme(Point $p) {
        $this->x = $p->getX();
        $this->y =$p->getY();
        $this->z = $p->getZ();
        $this->unite = $p->getUnite();
        return $this;
    }
}
 