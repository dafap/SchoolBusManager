<?php
/**
 * Description de la classe Facture
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource FactureInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Paiements;

use SbmCommun\Model\Db\ObjectData\Facture as ObjectDataFacture;

interface FactureInterface
{

    /**
     * Une nouvelle facture sera créée lorsque les signatures de la dernière facture et du
     * résultat obtenu par la méthode getResultats() sont différentes. Pour obtenir les
     * facturesPrecedentes on se contente de rechercher les factures de numéro antérieur à
     * celle qu'on doit sortir. En même temps on met à jour la propriété
     * 'montantDejaFacture'. La méthode traite la première facture en l'absence de
     * factures précédentes. La propriété oFacture est mise à jour.
     *
     * @return FactureInterface
     */
    public function facturer(): FactureInterface;

    /**
     * Renvoie la date de la facture oFacture
     *
     * @return string
     */
    public function getDate(): string;

    /**
     * Renvoie l'exercice de la facture oFacture
     *
     * @return int
     */
    public function getExercice(): int;

    /**
     * Renvoie un tableau des factures précédentes. Chaque enregistrement du tableau est
     * un tableau associatif dont les clés sont 'numero', 'date' et 'montant' (ici, numero
     * est compose du millesime suivi du numéro de facture avec un tiret comme séparateur)
     *
     * @return array
     */
    public function getFacturesPrecedentes(): array;

    /**
     * Renvoie le millesime de la facture oFacture
     *
     * @return int
     */
    public function getMillesime(): int;

    /**
     * Renvoie le montant TTC de la facture oFacture
     *
     * @return float
     */
    public function getMontant(): float;

    /**
     * Affecte le taux de TVA à appliquer
     *
     * @param float $taux
     * @return FactureInterface
     */
    public function setTauxTva(float $taux): FactureInterface;

    /**
     * Renvoie le montant de la TVA
     *
     * @return float
     */
    public function getTva(): float;

    /**
     * Renvoie la montant HT
     *
     * @return float
     */
    public function getMontantHT(): float;

    /**
     * Renvoie le montant déjà facturé
     *
     * @return float
     */
    public function getMontantDejaFacture(): float;

    /**
     * Renvoie le numéro de la facture contenue dans oFacture
     *
     * @return int
     */
    public function getNumero(): int;

    /**
     * Renvoie la propriété de même nom
     *
     * @return \SbmCommun\Model\Db\ObjectData\Facture
     */
    public function getOFacture(): ObjectDataFacture;

    /**
     * Renvoie la propriété de même nom
     *
     * @return int
     */
    public function getResponsableId(): int;

    /**
     * Renvoie le résultat après, si nécessaire, relancé les calculs
     *
     * @return \SbmCommun\Model\Paiements\ResultatsInterface
     */
    public function getResultats(): ResultatsInterface;

    /**
     * Lit les factures de ce responsable jusqu'au numéro indiqué et place cette facture
     * dans la propriété oFacture
     *
     * @param int $numero
     * @return FactureInterface
     */
    public function lire(int $numero): FactureInterface;

    /**
     * Affecte la propriété de même nom et en même temps, lance les calculs si nécessaire
     *
     * @param int $responsableId
     * @return FactureInterface
     */
    public function setResponsableId(int $responsableId): FactureInterface;
}