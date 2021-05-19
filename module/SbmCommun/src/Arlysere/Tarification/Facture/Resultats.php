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
 * @date 17 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

use SbmBase\Model\StdLib;
use SbmCommun\Model\Paiements\ResultatsInterface;

class Resultats implements ResultatsInterface
{

    const MONTANT_ABONNEMENTS_R1 = 'mntAboR1';

    const MONTANT_ABONNEMENTS_R2 = 'mntAboR2';

    const DETAIL_ABONNEMENTS_R1 = 'arrAboR1';

    const DETAIL_ABONNEMENTS_R2 = 'arrAboR2';

    const ENFANTS_R1 = 'r1';

    const ENFANTS_R2 = 'r2';

    const DUPLICATAS_R1 = 'mntDupR1';

    const DUPLICATAS_R2 = 'mntDupR2';

    const DUPLICATA_PU = 'mntDupPU';

    const PAIEMENTS_MONTANT = 'mntPai';

    const PAIEMENTS_DETAIL = 'arrPai';

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var int
     */
    private $responsableId;

    /**
     *
     * @var array
     */
    private $arrayEleveId;

    /**
     *
     * @var array
     */
    private $abonnements;

    /**
     *
     * @var array
     */
    private $duplicatas;

    /**
     *
     * @var array
     */
    private $enfants;

    /**
     *
     * @var array
     */
    private $paiements;

    public function __construct(int $millesime)
    {
        $this->millesime = $millesime;
        $this->responsableId = null;
        $this->abonnements = [
            'tous' => [],
            'inscrits' => [],
            'liste' => []
        ];
        $this->duplicatas = [
            self::DUPLICATA_PU => 0,
            'tous' => [],
            'liste' => []
        ];
        $this->enfants = [
            'tous' => [],
            'liste' => []
        ];
        $this->paiements = [];
    }

    /**
     *
     * @param int $millesime
     * @return self
     */
    public function setMillesime(int $millesime): self
    {
        $this->millesime = $millesime;
        return $this;
    }

    /**
     *
     * @param int $r
     *            1 pour R1, 2 pour R2
     * @param string $nature
     *            'tous'
     * @param array $arrayListe
     */
    public function setListeEleves(int $r, string $nature = 'tous', array $arrayListe)
    {
        $key = $r == 1 ? self::ENFANTS_R1 : self::ENFANTS_R2;
        $this->enfants[$nature][$key] = $arrayListe;
    }

    /**
     * Dans 'tous' on trouvera les enfants non gratuits et les gratuits ayant des
     * duplicatas
     *
     * @param int $r
     *            1 pour R1, 2 pour R2, sinon 0 pour tous
     * @param string $nature
     *            'tous' ou 'liste'
     * @return mixed|array
     */
    public function getListeEleves(int $r = 0, string $nature = 'tous')
    {
        if ($nature == 'tous' || $nature == 'liste') {
            if ($r) {
                $key = $r == 1 ? self::ENFANTS_R1 : self::ENFANTS_R2;
                return StdLib::getParamR([
                    $nature,
                    $key
                ], $this->enfants, []);
            } else {
                return StdLib::getParamR([
                    $nature,
                    self::ENFANTS_R1
                ], $this->enfants, []) +
                    StdLib::getParamR([
                        $nature,
                        self::ENFANTS_R2
                    ], $this->enfants, []);
            }
        }
        throw new \SbmCommun\Arlysere\Exception\OutOfBoundsException(
            'La nature indiquée est incorrecte.');
    }

    /**
     *
     * @param float $value
     */
    public function setDuplicataPU(float $value)
    {
        $this->duplicatas[self::DUPLICATA_PU] = $value;
    }

    /**
     *
     * @return float
     */
    public function getDuplicataPU(): float
    {
        return $this->duplicatas[self::DUPLICATA_PU];
    }

    /**
     *
     * @param int $r
     * @param string $nature
     * @param int $nb
     */
    public function setNbDuplicatas(int $r, string $nature, int $nb)
    {
        $key = $r == 1 ? self::DUPLICATAS_R1 : self::DUPLICATAS_R2;
        $this->duplicatas[$nature][$key]['nb'] = $nb;
    }

    /**
     *
     * @param int $r
     *            1 pour R1, 2 pour R2 ou 0 pour le total
     * @param string $nature
     *            'tous' ou 'liste'
     * @return int
     */
    public function getNbDuplicatas(int $r = 0, string $nature = 'tous'): int
    {
        if ($r) {
            $key = $r == 1 ? self::DUPLICATAS_R1 : self::DUPLICATAS_R2;
            return StdLib::getParamR([
                $nature,
                $key,
                'nb'
            ], $this->duplicatas, 0);
        } else {
            return StdLib::getParamR([
                $nature,
                self::DUPLICATAS_R1,
                'nb'
            ], $this->duplicatas, 0) +
                StdLib::getParamR([
                    $nature,
                    self::DUPLICATAS_R2,
                    'nb'
                ], $this->duplicatas, 0);
        }
    }

    /**
     *
     * @param int $r
     * @param string $nature
     * @param float $montantDuplicatas
     */
    public function setMontantDuplicatas(int $r, string $nature, float $montantDuplicatas)
    {
        $key = $r == 1 ? self::DUPLICATAS_R1 : self::DUPLICATAS_R2;
        $this->duplicatas[$nature][$key]['montant'] = $montantDuplicatas;
    }

    /**
     *
     * @param int $r
     *            1 pour R1, 2 pour R2 ou 0 pour le total
     * @param string $nature
     *            'tous' ou 'liste'
     * @return float
     */
    public function getMontantDuplicatas(int $r = 0, string $nature = 'tous'): float
    {
        if ($r) {
            $key = $r == 1 ? self::DUPLICATAS_R1 : self::DUPLICATAS_R2;
            return StdLib::getParamR([
                $nature,
                $key,
                'montant'
            ], $this->duplicatas, 0);
        } else {
            return StdLib::getParamR([
                $nature,
                self::DUPLICATAS_R1,
                'montant'
            ], $this->duplicatas, 0) +
                StdLib::getParamR([
                    $nature,
                    self::DUPLICATAS_R2,
                    'montant'
                ], $this->duplicatas, 0);
        }
    }

    /**
     * Renvoie le montal total à payer
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @return float
     */
    public function getMontantTotal($r = 0, string $nature = 'tous'): float
    {
        if ($nature == 'tous' || $nature == 'liste') {
            return $this->getAbonnementsMontant($r, $nature) +
                $this->getMontantDuplicatas($r);
        } else {
            throw new \SbmCommun\Arlysere\Exception\OutOfBoundsException(
                'La nature indiquée est incorrecte.');
        }
    }

    /**
     * Renvoie le solde
     *
     * @param
     *            int 1 pour R1, 2 pour R2, 0 pour prendre en compte à la fois R1 et R2
     * @param string $nature
     *            'tous', 'liste'
     * @return float
     */
    public function getSolde(int $r = 0, string $nature = 'tous'): float
    {
        return $this->getMontantTotal($r, $nature) - $this->getPaiementsMontant();
    }

    /**
     *
     * @return int
     */
    public function getResponsableId(): int
    {
        return $this->responsableId;
    }

    public function setArrayEleveId(array $arrayEleveId)
    {
        $this->arrayEleveId = $arrayEleveId;
    }

    public function setResponsableId(int $responsableId)
    {
        $this->responsableId = $responsableId;
    }

    /**
     * Vide le résultat
     *
     * @return self
     */
    public function clear(): self
    {
        $this->responsableId = null;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->responsableId);
    }

    /**
     *
     * @param int $r
     *            1 pour R2 sinon R2
     * @param string $nature
     *            'tous' ou 'inscrits'
     * @param float $montant
     */
    public function setAbonnementsMontant(int $r, string $nature, float $montant)
    {
        $key = $r == 1 ? self::MONTANT_ABONNEMENTS_R1 : self::MONTANT_ABONNEMENTS_R2;
        $this->abonnements[$nature][$key] = $montant;
    }

    /**
     * Pour obtenir le montant total des abonnements (R1 + R2), mettre 0 dans le premier
     * paramètre.
     * Si la nature est 'tous' laisser vide les 2 paramètres.
     *
     * @param int $r
     *            1 pour R1, 2 pour R2, 0 pour le total
     * @param string $nature
     *            'tous' ou 'incrits'
     * @return float
     */
    public function getAbonnementsMontant(int $r = 0, string $nature = 'tous'): float
    {
        if ($r) {
            $key = $r == 1 ? self::MONTANT_ABONNEMENTS_R1 : self::MONTANT_ABONNEMENTS_R2;
            return StdLib::getParamR([
                $nature,
                $key
            ], $this->abonnements, 0);
        } else {
            return StdLib::getParamR([
                $nature,
                self::MONTANT_ABONNEMENTS_R1
            ], $this->abonnements, 0) +
                StdLib::getParamR([
                    $nature,
                    self::MONTANT_ABONNEMENTS_R2
                ], $this->abonnements, 0);
        }
    }

    /**
     *
     * @param int $r
     *            1 pour R1, 2 pour R2
     * @param string $nature
     *            'tous' ou 'incrits'
     * @param array $arrayAbonnements
     */
    public function setAbonnementsDetail(int $r, string $nature, array $arrayAbonnements)
    {
        $key = $r == 1 ? self::DETAIL_ABONNEMENTS_R1 : self::DETAIL_ABONNEMENTS_R2;
        $this->abonnements[$nature][$key] = $arrayAbonnements;
    }

    /**
     * Renvoie le tableau des abonnements.
     * Si $r == 0, c'est un tableaud de tableau où la
     * première clé est le 1 ou 2 selon R1 ou R2
     *
     * @param int $r
     *            1 pour R1, 2 pour R2, 0 pour le total
     * @param string $nature
     *            'tous' ou 'incrits'
     * @return array
     */
    public function getAbonnementsDetail(int $r = 0, string $nature = 'tous'): array
    {
        if ($r) {
            $key = $r == 1 ? self::DETAIL_ABONNEMENTS_R1 : self::DETAIL_ABONNEMENTS_R2;
            return $this->abonnements[$nature][$key];
        } else {
            return $this->abonnements[$nature][self::DETAIL_ABONNEMENTS_R1] +
                $this->abonnements[$nature][self::DETAIL_ABONNEMENTS_R2];
        }
    }

    /**
     *
     * @param float $paiements
     */
    public function setPaiementsMontant(float $paiements)
    {
        $this->paiements[self::PAIEMENTS_MONTANT] = $paiements;
    }

    /**
     * Renvoie le montant des paiements de ce responsable
     *
     * @return float
     */
    public function getPaiementsMontant(): float
    {
        return StdLib::getParam(self::PAIEMENTS_MONTANT, $this->paiements, 0);
    }

    /**
     *
     * @param array $liste
     */
    public function setPaiementsDetail(array $liste)
    {
        $this->paiements[self::PAIEMENTS_DETAIL] = $liste;
    }

    /**
     *
     * @return array
     */
    public function getPaiementsDetail(): array
    {
        return StdLib::getParam(self::PAIEMENTS_DETAIL, $this->paiements, []);
    }

    /**
     * Renvoie une chaine de 32 caractères signature de la facture
     *
     * @return string
     */
    public function signature(): string
    {
        $tmp = sprintf("%04d%011d%.2f%.2f", $this->millesime, $this->responsableId,
            $this->getMontantDuplicatas(), $this->getMontantTotal());
        foreach ($this->getListeEleves() as $key => $value) {
            $tmp .= sprintf("%011d%02d%03d%d", $key, $value['grilleCode'],
                $value['duplicata'], $value['gratuit']);
        }
        foreach ($this->getAbonnementsDetail() as $abonnement) {
            $tmp .= sprintf("%02d%02d%.2f", $abonnement->getGrille(),
                $abonnement->getReduit(), $abonnement->getMontant());
        }
        return md5($tmp);
    }

    /**
     * Deux résultats sont égaux s'ils ont les mêmes éléments de facturation (duplicataR1,
     * liste d'élèves, abonnements).
     * Il n'est pas tenu compte des paiements.
     *
     * @param Resultats $r
     * @return boolean
     */
    public function equalTo(ResultatsInterface $r): bool
    {
        return $this->getMontantDuplicatas() == $r->getMontantDuplicatas() &&
            $this->getMontantTotal() == $r->getMontantTotal() &&
            $this->equalListeEleves($r->getListeEleves()) &&
            $this->getAbonnementsDetail() == $r->getAbonnementsDetail();
    }

    /**
     * Compare la listeEleves de l'objet à une autre listeEleves sans tenir compte du
     * paiement
     *
     * @param array $listeEleves
     * @return boolean
     */
    private function equalListeEleves(array $listeEleves): bool
    {
        if (array_keys($this->getListeEleves()) == array_keys($listeEleves)) {
            foreach ($this->getListeEleves() as $eleveId => $detail) {
                $other = $listeEleves[$eleveId];
                unset($detail['paiementR1'], $other['paiementR1']);
                if ($other != $detail) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}