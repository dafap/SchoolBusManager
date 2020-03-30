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
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmBase\Model\Session;
use SbmCommun\Arlysere\Tarification\GrilleTarifInterface;
use SbmCommun\Model\Strategy\TarifAttributs as TarifAttributsStrategy;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Tarifs extends AbstractSbmTable implements EffectifInterface, GrilleTarifInterface
{

    private $modes = [
        self::DEGRESSIF => 'dégressif',
        self::LINEAIRE => 'à l\'unité'
    ];

    private $mode_inconnu = "Le mode demandé est inconnu";

    private $reduits = [
        self::NORMAL => 'Normal',
        self::REDUIT => 'Réduit'
    ];

    private $reduit_inconnu = "L'application de la réduction est indéterminée";

    private $duplicatas = [
        self::ABONNEMENT => 'Abonnement',
        self::DUPLICATA => 'Duplicata'
    ];

    private $duplicata_inconnu = "La nature de ce tarif est inconnue";

    private $grilles = [
        self::TARIF_ARLYSERE => 'Résidents Arlysère',
        self::HORS_ARLYSERE => 'Résidents ou point de montée hors Arlysère',
        self::RPI => 'RPI École à École',
        self::CARTE_R2 => 'Double Domiciliation',
        self::DUPLICATA => 'Duplicata'
    ];

    private $grille_inconnu = "La grille demandée est inconnue";

    private $millesime;

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
            // 'duplicata' => new TarifAttributsStrategy($this->duplicatas,
            // $this->duplicata_inconnu),
            'grille' => new TarifAttributsStrategy($this->grilles, $this->grille_inconnu),
            // 'reduit' => new TarifAttributsStrategy($this->reduits,
            // $this->reduit_inconnu),
            'mode' => new TarifAttributsStrategy($this->modes, $this->mode_inconnu)
        ];
        $this->setMillesime();
    }

    public function setMillesime($millesime = null)
    {
        if (is_null($millesime)) {
            $this->millesime = Session::get('millesime');
        } else {
            $this->millesime = $millesime;
        }
        return $this;
    }

    // --------------- nomenclatures ------------------------
    public function getModes()
    {
        return $this->modes;
    }

    public function getDuplicatas()
    {
        return $this->duplicatas;
    }

    public function getGrilles()
    {
        $array = $this->grilles;
        unset($array[self::DUPLICATA]);
        return $array;
    }

    public function getReduits()
    {
        return $this->reduits;
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
     * Renvoie le montant d'un tarif connaissant la grille et la quantité NE CONVIENT PAS
     * POUR ARLYSERE
     *
     * @param int $grille
     * @param int $quantite
     *
     * @return float (currency)
     */
    public function getMontant(int $grille, int $quantite = 1)
    {
        return 0.0;
    }

    /**
     * SELECT grille, reduit, max(montant) AS m FROM `sbm_t_tarifs` WHERE duplicata=false
     * GROUP BY grille, reduit ORDER BY m DESC
     */
    public function getOrdreGrilles()
    {
        $select = $this->table_gateway->getSql()
            ->select()
            ->columns([
            'grille',
            'reduit'
        ])
            ->where([
            'duplicata' => 0,
            'millesime' => $this->millesime
        ])
            ->group([
            'grille',
            'reduit'
        ])
            ->order(new Expression('MAX(montant) DESC'));
        return $this->table_gateway->selectWith($select);
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
        $where->equalTo('grille', $grille)
            ->lessThanOrEqualTo('seuil', $seuil)
            ->equalTo('millesime', $this->millesime);
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
        $where->equalTo('millesime', $this->millesime);
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

