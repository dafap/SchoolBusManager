<?php
/**
 * Classe utilisée dans le template (sbm-pdf/layout/facture.phtml)
 *
 * @project sbm
 * @package SbmPdf/Model/Facture
 * @filesource Grille.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmPdf\Model\Facture;

class Grille
{

    private $listeGrilles;

    private $position;

    public function __construct()
    {
        $this->listeGrilles = [];
    }

    public function add($abonnement)
    {
        if (array_key_exists(10 * $abonnement->getGrille() + $abonnement->getReduit(),
            $this->listeGrilles)) {
            $this->listeGrilles[10 * $abonnement->getGrille() + $abonnement->getReduit()]->add(
                $abonnement);
        } else {
            $this->listeGrilles[10 * $abonnement->getGrille() + $abonnement->getReduit()] = new Produit(
                $abonnement);
        }
    }

    public function __invoke()
    {
        return $this->listeGrilles;
    }
}