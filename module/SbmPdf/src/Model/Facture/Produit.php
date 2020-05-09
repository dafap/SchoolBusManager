<?php
/**
 * Classe utilisée pour le template (sbm-pdf/layout/facture.phtml)
 * dans la classe Grille
 *
 * @project sbm
 * @package SbmPdf/Model/Facture
 * @filesource Produit.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmPdf\Model\Facture;

class Produit
{

    private $obj;

    private $quantite;

    private $montant;

    public function __construct($data)
    {
        $this->obj = $data;
        $this->quantite = 1;
        $this->montant = $data->getMontant();
    }

    public function add($data)
    {
        $this->montant += $data->getMontant();
        $this->quantite += 1;
    }

    public function __invoke()
    {
        return $this->obj->getTarifs()[1];
    }

    public function getMontant()
    {
        return $this->montant;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    public function getGrille()
    {
        return $this->obj->getGrille();
    }

    public function getReduit()
    {
        return $this->obj->getReduit();
    }
}