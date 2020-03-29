<?php
/**
 * Structure de résultat après analyse d'un responsable ou d'un groupe d'élèves
 *
 * Renvoie les résultats et les méthodes
 *
 * @project sbm
 * @package SbmCommun/src/Model/Paiements
 * @filesource Resultats.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Paiements;

use SbmBase\Model\StdLib;

class Resultats
{

    private const MONTANT_ABBONNEMENTS = 'montantAbonnements';

    private const MONTANT_DUPLICATAS = 'montantDuplicatas';

    private const MONTANT_PAIEMENTS = 'montantPaiements';

    private const DETAIL_ABONNEMENTS = 'detailAbonnements';

    private const DETAIL_PAIEMENTS = 'detailPaiements';

    private const LISTE_ELEVES = 'detailDuplicatas';

    private const NATURE_KEYS = [
        'tous',
        'liste'
    ];

    private const ABONNEMENT_KEYS = [
        'tous',
        'inscrits',
        'liste'
    ];

    private const STRUCTURE_ABONNEMENT_KEYS = [
        self::MONTANT_ABBONNEMENTS,
        self::DETAIL_ABONNEMENTS
    ];

    private const DETAIL_ABONNEMENT_KEYS = [
        'grille',
        'quantite',
        'montant'
    ];

    private const STRUCTURE_DUPLICATA_KEYS = [
        self::MONTANT_DUPLICATAS,
        self::LISTE_ELEVES
    ];

    private const LISTE_ELEVES_KEYS = [
        'nom',
        'prenom',
        'grilleTarifR1',
        'grilleCodeR1',
        'reductionR1',
        'duplicataR1',
        'paiementR1'
    ];

    /**
     * Le millesime va servir dans la signature
     *
     * @var int
     */
    private $millesime;

    /**
     * tableau de structure des abonnements avec les clés de la propriété `abonnements`
     * initialisée dans le constructeur. A chaque clé correspond un tableau vide ou
     * structuré comme décrit dans la méthode validArrayAbonnements
     *
     * @var array
     */
    private $abonnements;

    /**
     * tableau de structure des duplicatas avec les clés de la propriété `duplicatas`
     * initialisée dans le constructeur. A chaque clé correspond un tableau vide ou
     * structuré comme décrit dans la méthode validArrayDuplicatas
     *
     * @var array
     */
    private $duplicatas;

    /**
     * Pour le moment, c'est le montant total.
     *
     * @todo : à remplacer par une structure qui donne la liste des paiements et le
     *       montant total
     * @var float
     */
    private $paiements;

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
            'tous' => [],
            'liste' => []
        ];

        $this->paiements = [];
    }

    /**
     *
     * @param string $key
     * @return boolean
     */
    private function validAbonnementKey(string $key)
    {
        return in_array($key, self::ABONNEMENT_KEYS);
    }

    /**
     *
     * @param string $key
     * @return boolean
     */
    private function validNatureKey(string $key)
    {
        return in_array($key, self::NATURE_KEYS);
    }

    /**
     *
     * @param array $array
     * @return boolean
     */
    private function validArrayAbonnement(array $array)
    {
        $ok = true;
        foreach ($array as $row) {
            foreach (self::DETAIL_ABONNEMENT_KEYS as $key) {
                $ok &= array_key_exists($key, $row);
            }
        }
        return $ok;
    }

    /**
     *
     * @param array $array
     * @return boolean
     */
    private function validListeEleves(array $array)
    {
        $ok = true;
        foreach ($array as $row) {
            foreach (self::LISTE_ELEVES_KEYS as $key) {
                $ok &= array_key_exists($key, $row);
            }
        }
        return $ok;
    }

    /**
     * Renvoie la structure de nature précisée avec 'DETAIL_ABONNEMENTS' et
     * 'MONTANT_ABONNEMENTS' Renvoie les 3 structures si la nature n'est pas précisée
     *
     * @param null|string $nature
     *            'tous', 'inscrits' ou 'liste'
     * @return array|array[]
     */
    public function getAbonnements($nature = null)
    {
        if ($nature && $this->validAbonnementKey($nature)) {
            return StdLib::getParam($nature, $this->abonnements, []);
        }
        return $this->abonnements;
    }

    /**
     * Renvoie le montant ou 0 s'il n'existe pas
     *
     * @param string $nature
     *            'tous', 'inscrits' ou 'liste'
     * @return float
     */
    public function getAbonnementsMontant(string $nature = 'tous'): float
    {
        if ($this->validAbonnementKey($nature)) {
            return StdLib::getParamR([
                $nature,
                self::MONTANT_ABBONNEMENTS
            ], $this->abonnements, 0);
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature indiquée est incorrecte.');
        }
    }

    /**
     * Renvoie la liste des abonnements. Cette liste est composée d'enregistreents indexés
     * sur 'grilleCode' et présentant les clés suivantes :<ul> <li>grille</li>
     * <li>quantite</li> <li>montant</li></ul>
     *
     * @param string $nature
     *            'tous', 'inscrits' ou 'liste'
     * @return array
     */
    public function getAbonnementsDetail(string $nature = 'tous')
    {
        if ($this->validAbonnementKey($nature)) {
            return StdLib::getParamR([
                $nature,
                self::DETAIL_ABONNEMENTS
            ], $this->abonnements, []);
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature indiquée est incorrecte.');
        }
    }

    /**
     * Attention, le nom est trompeur ! Renvoie la structure décrite dans le constructeur.
     * Voir aussi la méthode getMontantDuplicatas()
     *
     * @return mixed
     */
    public function getDuplicatas()
    {
        return $this->duplicatas;
    }

    /**
     * Cette liste est composée d'enregistrements indexés par 'eleveId' et présentant les
     * clés suivantes : <ul><li>nom</li> <li>prenom</li> <li>grilleTarifR1</li>
     * <li>grilleCodeR1</li> <li>reductionR1</li> <li>grilleCodeR2</li>
     * <li>reductionR2</li> <li>duplicataR1</li> <li>paiementR1</li></ul>
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @return mixed
     */
    public function getListeEleves(string $nature = 'tous')
    {
        return StdLib::getParamR([
            $nature,
            self::LISTE_ELEVES
        ], $this->duplicatas, []);
    }

    /**
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @return float
     */
    public function getMontantDuplicatas(string $nature = 'tous'): float
    {
        return StdLib::getParamR([
            $nature,
            self::MONTANT_DUPLICATAS
        ], $this->duplicatas, 0);
    }

    /**
     *
     * @return mixed
     */
    public function getPaiements()
    {
        return $this->paiements;
    }

    /**
     *
     * @return float
     */
    public function getPaiementsMontant(): float
    {
        return StdLib::getParam(self::MONTANT_PAIEMENTS, $this->paiements, 0);
    }

    /**
     *
     * @return array
     */
    public function getPaiementsDetail()
    {
        return StdLib::getParam(self::DETAIL_PAIEMENTS, $this->paiements, []);
    }

    /**
     *
     * @return int
     */
    public function getResponsableId(): int
    {
        return $this->responsableId;
    }

    /**
     *
     * @return array
     */
    public function getArrayEleveId()
    {
        return $this->arrayEleveId;
    }

    /**
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @throws \SbmCommun\Model\Exception\OutOfBoundsException
     * @return number
     */
    public function getMontantTotal(string $nature = 'tous'): float
    {
        if ($this->validNatureKey($nature)) {
            if ($nature == 'tous') {
                $montantAbonnements = StdLib::getParamR(
                    [
                        'tous',
                        self::MONTANT_ABBONNEMENTS
                    ], $this->abonnements, 0);
            } else {
                $montantAbonnements = StdLib::getParamR(
                    [
                        'inscrits',
                        self::MONTANT_ABBONNEMENTS
                    ], $this->abonnements, 0) +
                    StdLib::getParamR([
                        'liste',
                        self::MONTANT_ABBONNEMENTS
                    ], $this->abonnements, 0);
            }
            return StdLib::getParamR([
                'tous',
                self::MONTANT_DUPLICATAS
            ], $this->duplicatas, 0) + $montantAbonnements;
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature indiquée est incorrecte.');
        }
    }

    /**
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @throws \SbmCommun\Model\Exception\OutOfBoundsException
     * @return number
     */
    public function getSolde(string $nature = 'tous')
    {
        if ($this->validNatureKey($nature)) {
            return $this->getMontantTotal($nature) - $this->getPaiementsMontant($nature);
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature indiquée est incorrecte.');
        }
    }

    /**
     *
     * @param string $nature
     *            'tous', 'inscrits' ou 'liste'
     * @param float $montant
     * @throws \SbmCommun\Model\Exception\OutOfBoundsException
     */
    public function setAbonnementsMontant(string $nature, float $montant)
    {
        if ($this->validAbonnementKey($nature)) {
            $this->abonnements[$nature][self::MONTANT_ABBONNEMENTS] = $montant;
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature des abonnements est incorrecte.');
        }
    }

    public function setAbonnementsDetail(string $nature, array $abonnements)
    {
        if ($this->validAbonnementKey($nature)) {
            if ($this->validArrayAbonnement($abonnements)) {
                $this->abonnements[$nature][self::DETAIL_ABONNEMENTS] = $abonnements;
            } else {
                throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                    'Le tableau des abonnements n\'est pas bien structuré.');
            }
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature des abonnements est incorrecte.');
        }
    }

    /**
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @param float $montantDuplicatas
     * @throws \SbmCommun\Model\Exception\OutOfBoundsException
     */
    public function setMontantDuplicatas(string $nature, float $montantDuplicatas)
    {
        if ($this->validNatureKey($nature)) {
            $this->duplicatas[$nature][self::MONTANT_DUPLICATAS] = $montantDuplicatas;
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'Nature incorrecte.');
        }
    }

    /**
     *
     * @param string $nature
     *            'tous' ou 'liste'
     * @param array $listeEleves
     * @throws \SbmCommun\Model\Exception\OutOfBoundsException
     */
    public function setListeEleves(string $nature, array $listeEleves)
    {
        if ($this->validNatureKey($nature)) {
            if ($this->validListeEleves($listeEleves)) {
                $this->duplicatas[$nature][self::LISTE_ELEVES] = $listeEleves;
            } else {
                throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                    'La liste des élèves n\'est pas bien structuré.');
            }
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'Nature incorrecte.');
        }
    }

    /**
     *
     * @param float $paiements
     */
    public function setPaiementsTotal(float $paiements)
    {
        $this->paiements[self::MONTANT_PAIEMENTS] = $paiements;
    }

    /**
     *
     * @param array $liste
     */
    public function setPaiementsDetail(array $liste)
    {
        $this->paiements[self::DETAIL_PAIEMENTS] = $liste;
    }

    /**
     *
     * @param int $responsableId
     */
    public function setResponsableId(int $responsableId)
    {
        $this->responsableId = $responsableId;
    }

    /**
     *
     * @param mixed $arrayEleveId
     */
    public function setArrayEleveId(array $arrayEleveId = null)
    {
        $this->arrayEleveId = $arrayEleveId;
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
     * Renvoie une chaine de 32 caractères signature de la facture
     *
     * @return string
     */
    public function signature()
    {
        $tmp = sprintf("%04d%011d%.2f%.2f", $this->millesime, $this->responsableId,
            $this->getMontantDuplicatas(), $this->getMontantTotal());
        foreach ($this->getListeEleves() as $key => $value) {
            $tmp .= sprintf("%011d%02d%03d%d%d", $key, $value['grilleCode'],
                $value['duplicataR1'], $value['fa'], $value['gratuit']);
        }
        foreach ($this->getAbonnementsDetail() as $key => $value) {
            $tmp .= sprintf("%02d%02d%.2f", $key, $value['quantite'], $value['montant']);
        }
        return md5($tmp);
    }

    /**
     * Deux résultats sont égaux s'ils ont les mêmes éléments de facturation (duplicataR1,
     * liste d'élèves, abonnements). Il n'est pas tenu compte des paiements.
     *
     * @param Resultats $r
     * @return boolean
     */
    public function equalTo(Resultats $r): bool
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