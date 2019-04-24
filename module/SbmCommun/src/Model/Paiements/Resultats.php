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
 * @date 17 avr. 2019
 * @version 2019-4.5
 */
namespace SbmCommun\Model\Paiements;

class Resultats
{

    private $abonnements;

    private $duplicatas;

    private $paiements;

    private $responsableId;

    private $arrayEleveId;

    private $abonnementKeys;

    private $natureKeys;

    public function __construct()
    {
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
        $this->natureKeys = [
            'tous',
            'liste'
        ];
        $this->abonnementKeys = [
            'tous',
            'inscrits',
            'liste'
        ];
    }

    private function validAbonnementKey($key)
    {
        return in_array($key, $this->abonnementKeys);
    }

    private function validNatureKey($key)
    {
        return in_array($key, $this->natureKeys);
    }

    private function validArrayAbonnement($array)
    {
        $ok = array_key_exists('montantAbonnements', $array);
        $ok &= array_key_exists('detailAbonnements', $array);
        foreach ($array['detailAbonnements'] as $row) {
            $ok &= array_key_exists('grille', $row);
            $ok &= array_key_exists('quantite', $row);
            $ok &= array_key_exists('montant', $row);
        }
        return $ok;
    }

    private function validArrayDuplicata($array)
    {
        $ok = array_key_exists('montantDuplicatas', $array);
        $ok &= array_key_exists('detailDuplicatas', $array);
        foreach ($array['detailDuplicatas'] as $row) {
            $ok &= array_key_exists('nom', $row);
            $ok &= array_key_exists('prenom', $row);
            $ok &= array_key_exists('grilleTarif', $row);
            $ok &= array_key_exists('duplicata', $row);
        }
        return $ok;
    }

    /**
     *
     * @return mixed
     */
    public function getAbonnements($nature = null)
    {
        if ($nature) {
            return $this->abonnements[$nature];
        }
        return $this->abonnements;
    }

    /**
     *
     * @return mixed
     */
    public function getDuplicatas()
    {
        return $this->duplicatas;
    }

    public function getDetailListeEleve()
    {
        return $this->duplicatas['detailDuplicatas'];
    }

    /**
     *
     * @return mixed
     */
    public function getPaiements()
    {
        return $this->paiements ?: 0;
    }

    /**
     *
     * @return mixed
     */
    public function getResponsableId()
    {
        return $this->responsableId;
    }

    /**
     *
     * @return mixed
     */
    public function getArrayEleveId()
    {
        return $this->arrayEleveId;
    }

    public function getMontantTotal(string $nature = 'tous')
    {
        if ($this->validNatureKey($nature)) {
            if ($nature == 'tous') {
                $montantAbonnements = $this->abonnements['tous']['montantAbonnements'];
            } else {
                $montantAbonnements = $this->abonnements['inscrits']['montantAbonnements'] +
                    $this->abonnements['liste']['montantAbonnements'];
            }
            return $this->duplicatas['tous']['montantDuplicatas'] + $montantAbonnements;
            ;
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature indiquée est incorrecte.');
        }
    }

    public function getSolde(string $nature = 'tous')
    {
        if ($this->validNatureKey($nature)) {
            return $this->getMontantTotal($nature) - $this->getPaiements();
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'La nature indiquée est incorrecte.');
        }
    }

    /**
     *
     * @param string $nature
     * @param array $abonnements
     */
    public function setAbonnements(string $nature, array $abonnements)
    {
        if ($this->validAbonnementKey($nature)) {
            if ($this->validArrayAbonnement($abonnements)) {
                $this->abonnements[$nature] = $abonnements;
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
     * @param array $duplicatas
     */
    public function setDuplicatas(string $nature, array $duplicatas)
    {
        if ($this->validNatureKey($nature)) {
            if ($this->validArrayDuplicata($duplicatas)) {
                $this->duplicatas[$nature] = $duplicatas;
            } else {
                throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                    'Le tableau des duplicatas n\'est pas bien structuré.');
            }
        } else {
            throw new \SbmCommun\Model\Exception\OutOfBoundsException(
                'Nature incorrecte.');
        }
    }

    /**
     *
     * @param mixed $paiements
     */
    public function setPaiements($paiements)
    {
        $this->paiements = $paiements;
    }

    /**
     *
     * @param mixed $responsableId
     */
    public function setResponsableId($responsableId)
    {
        $this->responsableId = $responsableId;
    }

    /**
     *
     * @param mixed $arrayEleveId
     */
    public function setArrayEleveId($arrayEleveId)
    {
        $this->arrayEleveId = $arrayEleveId;
    }

    /**
     *
     * @return boolean
     */
    function isEmpty()
    {
        return empty($this->responsableId);
    }
}