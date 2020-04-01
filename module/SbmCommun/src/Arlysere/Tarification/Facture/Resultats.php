<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmCommun/Arlysere/Tarification/Facture
 * @filesource Resultats.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

class Resultats
{

    /**
     * Renvoie le montal total à payer
     *
     * @return number
     */
    public function getMontantTotal()
    {
        return 0.00;
    }

    /**
     * Renvoie le montant des paiements de ce responsable
     *
     * @return number
     */
    public function getPaiementsMontant()
    {
        return 0.00;
    }

    /**
     * Renvoie le solde
     *
     * @return number
     */
    public function getSolde()
    {
        return 0.00;
    }
}