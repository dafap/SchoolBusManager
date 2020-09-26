<?php
/**
 * Classe contenant les méthodes de manipulation de refdet : création, extraction des différentes parties
 *
 * Soit on passe les différentes parties nécessaires à la construction de refdet, soit on passe refdet.
 *
 * Exemple 1 : Obtenir la référence
 * $refdet = new Refdet($this->getFormulaireAbonnement());
 * $refdet->setMillesime($this->millesime)
 *        ->setExercice($this->exercice)
 *        ->setFactureNumero($this->facture->getNumero())
 *        ->setResponsableId($this->responsable->responsableId)
 *        ->setNbEnfants($this->nbEnfants)
 *        ->setPaiement3fois($this->paiement3fois)
 *        ->setAbonnementMontant($this->getMontantAbonnement())
 *        ->getRefdet();
 *
 * Exemple 2 : Extraire une propriété de la référence (ici, responsableId)
 * $refdet = new Refdet($this->getFormulaireAbonnement());
 * $refdet->setRefdet($my_reference);
 * $responsableId = $refdet->getResponsableId();
 *
 * @project sbm
 * @package
 * @filesource Refdet.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Plugin\PayBox;

class Refdet
{

    /**
     *
     * @var string
     */
    private $refdet;

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var int
     */
    private $exercice;

    /**
     *
     * @var int
     */
    private $factureNumero;

    /**
     *
     * @var int
     */
    private $responsableId;

    /**
     *
     * @var int
     */
    private $nbEnfants;

    /**
     *
     * @var bool
     */
    private $paiement3fois;

    /**
     *
     * @var array
     */
    private $abonnementConfig;

    /**
     *
     * @var int
     */
    private $abonnementMontant;

    /**
     * Initialise les propriétés
     *
     * @param array $abonnementConfig
     */
    public function __construct(array $abonnementConfig = [])
    {
        $this->clearRefdet();
        $this->setAbonnementConfig($abonnementConfig);
    }

    /**
     * Efface tout sauf $abonnementConfig
     */
    public function clearRefdet()
    {
        $this->setRefdet('')
            ->setMillesime(0)
            ->setExercice(0)
            ->setFactureNumero(0)
            ->setResponsableId(0)
            ->setNbEnfants(0)
            ->setPaiement3fois(false)
            ->setAbonnementMontant(0);
    }

    /**
     * Renvoie RefDet. Le crée si besoin.
     *
     * @return string
     */
    public function getRefdet(): string
    {
        if (! $this->refdet) {
            $refdet = sprintf("%4d%4s%14s%07d%07d%02d", $this->millesime, $this->exercice,
                date('YmdHis'), $this->factureNumero, $this->responsableId,
                $this->nbEnfants);
            if ($this->paiement3fois) {
                $abonnementComposition = $this->abonnementConfig;
                $abonnementComposition['PBX_2MONT'] = sprintf('%010d',
                    $this->abonnementMontant);
                foreach ($abonnementComposition as $key => $value) {
                    $refdet .= $key . $value;
                }
            }
            $this->refdet = $refdet;
        }
        return $this->refdet;
    }

    /**
     *
     * @return number
     */
    public function getMillesime(): int
    {
        return (int) substr($this->getRefdet(), 0, 4);
    }

    /**
     *
     * @return number
     */
    public function getExercice(): int
    {
        return (int) substr($this->getRefdet(), 4, 4);
    }

    /**
     *
     * @return string
     */
    public function getDate(): string
    {
        return substr($this->getRefdet(), 8, 14);
    }

    /**
     *
     * @return number
     */
    public function getFactureNumero(): int
    {
        return (int) substr($this->getRefdet(), 22, 7);
    }

    /**
     *
     * @return number
     */
    public function getResponsableId(): int
    {
        return (int) substr($this->getRefdet(), 29, 7);
    }

    /**
     *
     * @return number
     */
    public function getNbEnfants(): int
    {
        return substr($this->getRefdet(), 36, 2);
    }

    /**
     *
     * @return boolean
     */
    public function isPaiement3fois(): bool
    {
        $refdet = $this->getRefdet();
        return strlen($refdet) == 90 && strpos($refdet, 'PBX_2MONT');
    }

    /**
     *
     * @return number|string (valeur ou chaine vide)
     */
    public function getAbonnementMontant()
    {
        if ($this->isPaiement3fois()) {
            return (int) substr($this->getRefdet(),
                strpos($this->refdet, 'PBX_2MONT') + strlen('PBX_2MONT'), 10);
        } else {
            return '';
        }
    }

    public function getAbonnementNbPaiements()
    {
        if ($this->isPaiement3fois()) {
            $pos = strpos($this->refdet, 'PBX_NBPAIE');
            if ($pos) {
                return (int) substr($this->getRefdet(), $pos + strlen('PBX_NBPAIE'), 2);
            }
        }
        return '';
    }

    public function getAbonnementFrequence()
    {
        if ($this->isPaiement3fois()) {
            $pos = strpos($this->refdet, 'PBX_FREQ');
            if ($pos) {
                return (int) substr($this->getRefdet(), $pos + strlen('PBX_FREQ'), 2);
            }
        }
        return '';
    }

    public function getAbonnementQuand()
    {
        if ($this->isPaiement3fois()) {
            $pos = strpos($this->refdet, 'PBX_QUAND');
            if ($pos) {
                return (int) substr($this->getRefdet(), $pos + strlen('PBX_QUAND'), 2);
            }
        }
        return '';
    }

    public function getAbonnementDelais()
    {
        if ($this->isPaiement3fois()) {
            $pos = strpos($this->refdet, 'PBX_DELAIS');
            if ($pos) {
                return (int) substr($this->getRefdet(), $pos + strlen('PBX_DELAIS'), 3);
            }
        }
        return '';
    }

    /**
     *
     * @param string $refdet
     */
    public function setRefdet($refdet)
    {
        $this->refdet = $refdet;
        return $this;
    }

    /**
     *
     * @param number $millesime
     */
    public function setMillesime(int $millesime)
    {
        $this->millesime = $millesime;
        return $this;
    }

    /**
     *
     * @param number $exercice
     */
    public function setExercice(int $exercice)
    {
        $this->exercice = $exercice;
        return $this;
    }

    /**
     *
     * @param number $facture_numero
     */
    public function setFactureNumero(int $facture_numero)
    {
        $this->factureNumero = $facture_numero;
        return $this;
    }

    /**
     *
     * @param number $responsableId
     */
    public function setResponsableId(int $responsableId)
    {
        $this->responsableId = $responsableId;
        return $this;
    }

    /**
     *
     * @param number $nbEnfants
     */
    public function setNbEnfants(int $nbEnfants)
    {
        $this->nbEnfants = $nbEnfants;
        return $this;
    }

    /**
     *
     * @param boolean $paiement3fois
     */
    public function setPaiement3fois(bool $paiement3fois)
    {
        $this->paiement3fois = $paiement3fois;
        return $this;
    }

    /**
     *
     * @param array $abonnementConfig
     */
    public function setAbonnementConfig(array $abonnementConfig)
    {
        $this->abonnementConfig = $abonnementConfig;
        return $this;
    }

    /**
     *
     * @param number $abonnementMontant
     */
    public function setAbonnementMontant(int $abonnementMontant)
    {
        $this->abonnementMontant = $abonnementMontant;
        return $this;
    }
}