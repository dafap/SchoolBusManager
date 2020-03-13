<?php
/**
 * Abonnement correspondant à un élève
 *
 * Cette classe reçoit dans son constructeur un eleveId, sa grille tarifaire ainsi que la
 * table des tarifs de cette grille.
 * Au moment de sa construction, le montant à payer est est le montant de seuil le plus bas
 * de sa grille tarifaire (en général le montant le plus élevé).
 * Elle présente 2 méthodes publiques :
 * __invoke() permet de renvoyer le montant
 * appliquerMontant($rang_fratrie) permet d'affecter le montant de la grille tarifaire en
 * fonction du rang de l'élève dans la fratrie.
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Taririfation/Facture;
 * @filesource Abonnement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

class Abonnement
{

    /**
     *
     * @var int
     */
    private $grille;

    /**
     *
     * @var float
     */
    private $montant;

    /**
     *
     * @var array
     */
    private $tarifs;

    /**
     *
     * @var int
     */
    private $eleveId;

    /**
     *
     * @return number
     */
    public function getGrille()
    {
        return $this->grille;
    }

    /**
     *
     * @return number
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     *
     * @return array
     */
    public function getTarifs()
    {
        return $this->tarifs;
    }

    /**
     *
     * @return number
     *
     * @return \SbmCommun\Arlysere\Tarification\Facture\Abonnement
     */
    public function getEleveId()
    {
        return $this->eleveId;
        return $this;
    }

    /**
     *
     * @param number $grille
     *
     * @return \SbmCommun\Arlysere\Tarification\Facture\Abonnement
     */
    public function setGrille($grille)
    {
        $this->grille = $grille;
        return $this;
    }

    /**
     *
     * @param array $tarifs
     */
    public function setTarifs($tarifs)
    {
        $this->tarifs = $tarifs;
        $this->initMontant();
    }

    /**
     *
     * @param number $eleveId
     *
     * @return \SbmCommun\Arlysere\Tarification\Facture\Abonnement
     */
    public function setEleveId($eleveId)
    {
        $this->eleveId = $eleveId;
        return $this;
    }

    /**
     *
     * @param int $grille
     * @param array $tarifs
     * @param int $eleveId
     */
    public function __construct($grille, $tarifs, $eleveId)
    {
        $this->eleveId = $eleveId;
        $this->grille = $grille;
        $this->tarifs = $tarifs;
        $this->initMontant();
    }

    /**
     * Applique le tarif de seuil le plus bas de cette grille en l'affectant à montant
     */
    private function initMontant()
    {
        ksort($this->tarifs);
        $this->montant = reset($this->tarifs);
    }

    /**
     * Renvoie le montant
     *
     * @return number
     */
    public function __invoke()
    {
        return $this->montant;
    }

    /**
     * Applique le tarif de la grille correspondant au rang de fratrie indiqué en
     * l'affectant à montant
     *
     * @param int $rang_fratrie
     */
    public function appliquerMontant($rang_fratrie)
    {
        foreach ($this->tarifs as $seuil => $montant) {
            if ($rang_fratrie <= $seuil) {
                break;
            }
        }
        $this->montant = $montant;
    }
}