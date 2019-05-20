<?php
/**
 * Gestion de la table `tarifs`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Tarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Paiements\GrilleTarifInterface;
use SbmCommun\Model\Strategy\TarifAttributs as TarifAttributsStrategy;
use Zend\Db\Sql\Where;

class Tarifs extends AbstractSbmTable implements EffectifInterface, GrilleTarifInterface
{
    private $modes = [
        self::DEGRESSIF => 'dégressif',
        self::LINEAIRE => 'à l\'unité'
    ];

    private $mode_inconnu = "Le mode demandé est inconnu";

    private $rythmes = [
        1 => 'annuel',
        2 => 'semestriel',
        3 => 'trimestriel',
        4 => 'mensuel'
    ];

    private $rythme_inconnu = "Le rythme demandé est inconnu";

    private $grilles = [
        self::DP_PLEIN_TARIF=> 'DP ayants droit',
        self::DP_DEMI_TARIF => 'DP en GA demi tarif',
        self::INTERNE => 'Interne',
        self::NON_AYANT_DROIT => 'Non ayant droit',
        self::DUPLICATA => 'Duplicata'
    ];

    private $grille_inconnu = "La grille demandée est inconnue";

    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'tarifs';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Tarifs';
        $this->id_name = 'tarifId';
        $this->strategies = [
            'rythme' => new TarifAttributsStrategy($this->rythmes, $this->rythme_inconnu),
            'grille' => new TarifAttributsStrategy($this->grilles, $this->grille_inconnu),
            'mode' => new TarifAttributsStrategy($this->modes, $this->mode_inconnu)
        ];
    }

    // --------------- nomenclatures ------------------------
    public function getModes()
    {
        return $this->modes;
    }

    public function getRythmes()
    {
        return $this->rythmes;
    }

    public function getGrilles()
    {
        return $this->grilles;
    }

    public function getGrille(int $grille)
    {
        if (! array_key_exists($grille, $this->grilles)) {
            throw new Exception\OutOfBoundsException($this->grille_inconnu);
        }
        return $this->grilles[$grille];
    }

    public function getDuplicataCodeGrille()
    {
        return self::DUPLICATA;
    }

    // ------------- recherche de données -----------------

    /**
     * Renvoie le montant d'un tarif connaissant la grille et la quantité
     *
     * @param int $grille
     * @param int $quantite
     *
     * @return float (currency)
     */
    public function getMontant(int $grille, int $quantite = 1)
    {
        if ($quantite >= 1) {
            $where = new Where();
            $where->equalTo('grille', $grille)->lessThanOrEqualTo('seuil', $quantite);
            $resultset = $this->fetchAll($where, 'seuil DESC');
            $row = $resultset->current();
            // selon que la strategy est appliquée ou non
            if ($row->mode == self::DEGRESSIF ||
                $row->mode == $this->modes[self::DEGRESSIF]) {
                return $row->montant;
            } else {
                return $row->montant * $quantite;
            }
        } else {
            return 0.0;
        }
    }

    /**
     * Renvoie l'identifiant d'un tarif connaissant la grille et la quantité
     *
     * @param int $grille
     * @param int $quantite
     *
     * @return integer
     */
    public function getTarifId(int $grille, int $seuil = 1)
    {
        $where = new Where();
        $where->equalTo('grille', $grille)->lessThanOrEqualTo('seuil', $seuil);
        $resultset = $this->fetchAll($where, 'seuil DESC');
        $row = $resultset->current();
        return $row->tarifId;
    }

    /**
     * Renvoie le tableau de grilles de tarifs [grille => [seuil => montant, ...], ...]
     *
     * @param int $grille
     * @param int $seuil
     *
     * @return array
     */
    public function getTarifs(int $grille = null, int $seuil = null)
    {
        $where = new Where();
        if ($grille) {
            $where->equalTo('grille', $grille);
        }
        if ($seuil) {
            $where->equalTo('seuil', $seuil);
        }
        $resultset = $this->fetchAll($where);
        $result = [];
        foreach ($resultset as $row) {
            $result[$row->grille][$row->seuil] = $row->montant;
        }
        return $result;
    }

    /**
     * Mise à jour de la colonne `selection`
     *
     * @param int $tarifId
     * @param int $selection
     *            0 ou 1
     */
    public function setSelection($tarifId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'tarifId' => $tarifId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }
}

