<?php
/**
 * Construction des pictogrammes de début de ligne pour les listes
 * 
 * Usage :
 * - définir les pictogrammes d'une ligne
 *   $this->pictogrammes('init')->addPreinscrit($condition)->addSansAffectations($nb_affectations);
 * - ajouter un pictogramme
 *   $this->pictogrammes(true)->addPreinscrit($condition);
 * - renvoyer le code dans la vue
 *   echo $this->prictogrammes();
 *
 * @project sbm
 * @package SbmCommun/Form/View/Helper
 * @filesource Pictogrammes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Form\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Pictogrammes extends AbstractHelper
{

    private $pictogrammes;

    public function __construct()
    {
        $this->pictogrammes = [];
    }

    /**
     * Pour une nouvelle ligne, penser à initialiser sinon les pictogrammes se rajoutent
     * à ceux de la ligne précédente.
     *
     * @param string $init
     *            si 'init' on renvoie une structure vide,
     *            si null on renvoie le code
     *            sinon on renvoie la structure pour la faire suivre d'une méthode d'ajout
     *            
     * @return string|\SbmCommun\Form\View\Helper\Pictogrammes
     */
    public function __invoke($init = null)
    {
        if (is_null($init)) {
            return implode(' ', $this->pictogrammes);
        } elseif ($init == 'init') {
            return $this->init();
        } else {
            return $this;
        }
    }

    public function init()
    {
        $this->pictogrammes = [];
        return $this;
    }

    public function addPreinscrit($preinscrit)
    {
        if ($preinscrit) {
            $this->pictogrammes[] = '<i class="fam-tag-orange" title="Préinscrit"></i>';
        }
        return $this;
    }

    /**
     * Place un pictogramme s'il n'y a pas d'affectation
     *
     * @param int|bool $affectations
     *            nombre d'affectations ou booléen indiquant s'il y a au moins une affectation
     */
    public function addSansAffectation($affectations)
    {
        if (! $affectations) {
            $this->pictogrammes[] = '<i class="fam-chart-line-error" title="Sans affectation"></i>';
        }
        return $this;
    }

    public function addDistanceZero($demande1, $distance1, $demande2, $distance2)
    {
        if (($demande1 > 0 && $distance1 == 0.0) || ($demande2 > 0 && $distance2 == 0.0)) {
            $this->pictogrammes[] = '<i class="fam-cog-error" title="Vérifier les distances"></i>';
        }
        return $this;
    }
}