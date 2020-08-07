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
 * @date 7 août 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\View\Helper;

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
     * @return string|\SbmCommun\Model\View\Helper\Pictogrammes
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
            $this->pictogrammes[] = '<i class="fam-tag-orange" title="Impayé"></i>';
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
        $zero1 = $distance1 == 0.0 || $distance1 == 99;
        $zero2 = $distance2 == 0.0 || $distance2 == 99;
        if (($demande1 > 0 && $zero1) || ($demande2 > 0 && $zero2)) {
            $this->pictogrammes[] = '<i class="fam-cog-error" title="Vérifier les distances"></i>';
        }
        return $this;
    }

    public function addSansPhoto($sansphoto)
    {
        if ($sansphoto) {
            $this->pictogrammes[] = '<i class="fam-camera-error" title="Sans photo"></i>';
        }
        return $this;
    }

    public function addEnAttente($enAttente)
    {
        if ($enAttente) {
            $this->pictogrammes[] = '<i class="fam-cross" title="En attente"';
        }
        return $this;
    }

    public function addSansCarte($sansCarte)
    {
        if ($sansCarte) {
            $this->pictogrammes[] = '<i class="fam-vcard-delete" title="Carte non tirée"';
        }
        return $this;
    }
}