<?php
/**
 * Gestion de la table `paiements`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Paiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 oct. 2021
 * @version 2021-2.6.4
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmBase\Model\DateLib;
use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use SbmBase\Model\StdLib;

class Paiements extends AbstractSbmTable
{

    const MAXI = 200;

    /**
     * Le code espèces est en général 2.
     * L'initialisation à 999 permet de retrouver un comportement standard si le code
     * espèces n'est pas initialisé.
     *
     * @var int
     */
    private $codeEspeces = 999;

    /**
     * Initialisation de la station
     */
    protected function init()
    {
        $this->table_name = 'paiements';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Paiements';
        $this->id_name = 'paiementId';
    }

    /**
     * Précise le code espèces
     *
     * @param int $code
     * @return \SbmCommun\Model\Db\Service\Table\Paiements
     */
    public function setCodeEspeces(int $code)
    {
        $this->codeEspeces = $code;
        return $this;
    }

    /**
     * Dans le cas d'un paiement en espèces et si le codeEspeces a été initialisé, la
     * référence est complétée par le timestamp pour garantir l'unicité.
     * La référence initiale est tronquée à 70 caractères si elle est donnée. Sinon, elle
     * prend la valeur 'Espèces'.
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        $tmp = $obj_data->dateValeur;
        if (empty($tmp)) {
            $dte = new \DateTime($obj_data->datePaiement);
            $obj_data->dateValeur = $dte->format('Y-m-d');
        }
        if ($obj_data->codeModeDePaiement == $this->codeEspeces) {
            $obj_data->reference = $this->referencePourEspeces($obj_data->reference);
        }
        return parent::saveRecord($obj_data);
    }

    private function referencePourEspeces(string $obj_data_reference): string
    {
        $obj_data_reference = preg_replace('/(.*)\s\(\§\d*\§\)/', '$1',
            $obj_data_reference ?: 'Espèces');
        $dte = new \DateTime();
        $suffixe = ' (§' . $dte->format('U') . '§)';
        $maxLen = StdLib::getParam('reference',
            $this->db_manager->getMaxLengthArray($this->table_name, $this->table_type), 30);
        return substr($obj_data_reference, 0, $maxLen - strlen($suffixe)) . $suffixe;
    }

    /**
     * Enregistre l'état du champ selection
     *
     * @param int $paiementId
     * @param int $selection
     *            0 ou 1
     */
    public function setSelection($paiementId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'paiementId' => $paiementId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }

    /**
     * Renvoie le paiementId d'un paiement pour les paramètres indiqués.
     * Renvoie null si
     * le paiement n'est pas enregistré
     *
     * @param int $responsableId
     * @param string $dateValeur
     * @param string $reference
     *
     * @return integer
     */
    public function getPaiementId($responsableId, $dateValeur, $reference)
    {
        $where = new Where();
        $rowset = $this->fetchAll(
            $where->equalTo('responsableId', $responsableId)
                ->equalTo('dateValeur', $dateValeur)
                ->equalTo('reference', $reference));
        if ($rowset && $rowset->current()) {
            return $rowset->current()->paiementId;
        } else {
            return null;
        }
    }

    /**
     * Marque la date dans dateBordereau pour les paiements non déposés dont <ul> <li>la
     * date de valeur est antérieure à date indiquée</li> <li>qui correspondent au mode de
     * paiement et à la caisse indiqués</li></ul>
     *
     * @param string $date
     *            format dateMysql
     * @param int $codeModeDePaiement
     *            code du mode de paiment concerné
     * @param int $codeCaisse
     *            code de la caisse concernée
     * @param int|null $exercice
     *            exercice budgétaire concerné
     * @param int|null $anneeScolaire
     *            année scolaire concernée
     * @return int Nombre de lignes du bordereau
     */
    public function marqueBordereau($date, $codeModeDePaiement, $codeCaisse,
        $exercice = null, $anneeScolaire = null)
    {
        $where = new Where();
        $where->equalTo('codeModeDePaiement', $codeModeDePaiement)
            ->equalTo('codeCaisse', $codeCaisse)
            ->literal('mouvement = 1')
            ->isNull('dateDepot')
            ->lessThanOrEqualTo('dateValeur', $date);
        if (! is_null($exercice)) {
            $where->equalTo('exercice', $exercice);
        }
        if (! is_null($anneeScolaire)) {
            $where->equalTo('anneeScolaire', $anneeScolaire);
        }
        $select = $this->table_gateway->getSql()->select();
        $select->where($where)
            ->order('paiementId DESC')
            ->limit(self::MAXI);
        $resultset = $this->table_gateway->selectWith($select);
        if ($resultset->count()) {
            $paiementIdMaxi = $resultset->current()->paiementId;
        } else {
            $paiementIdMaxi = - 1;
        }
        $where->lessThanOrEqualTo('paiementId', $paiementIdMaxi);
        return $this->table_gateway->update([
            'dateBordereau' => $date
        ], $where);
    }

    /**
     * Annule un bordereau non déposé
     *
     * @param string $dateBordereau
     *            format dateMysql
     * @param int $codeModeDePaiement
     *            code du mode de paiement du bordereau à annuler
     * @return int Nombre de lignes du bordereau annulé
     */
    public function annuleBordereau($dateBordereau, $codeModeDePaiement)
    {
        $where = new Where();
        $where->equalTo('codeModeDePaiement', $codeModeDePaiement)
            ->equalTo('dateBordereau', $dateBordereau)
            ->isNull('dateDepot');
        return $this->table_gateway->update([
            'dateBordereau' => null
        ], $where);
    }

    /**
     * Clôture un dépot
     *
     * @param string $dateBordereau
     *            format dateMysql
     * @param int $codeModeDePaiement
     *            mode de paiement concerné par ce bordereau
     * @param int $codeCaisseComptable
     *            code caisse du comptable
     * @return int
     */
    public function clotureDepot($dateBordereau, $codeModeDePaiement, $codeCaisseComptable)
    {
        $dateDepot = DateLib::nowToMysql();
        $where = new Where();
        $where->equalTo('codeModeDePaiement', $codeModeDePaiement)
            ->equalTo('dateBordereau', $dateBordereau)
            ->isNull('dateDepot');
        return $this->table_gateway->update(
            [
                'dateDepot' => $dateDepot,
                'codeCaisse' => $codeCaisseComptable
            ], $where);
    }

    /**
     * Annule le dépôt d'un bordereau sans annuler le bordereau et replace les paiements
     * dans la caisse indiquée.
     *
     * @param string $dateDepot
     *            format dateMysql
     * @param string $dateBordereau
     *            format dateMysql
     * @param int $codeModeDePaiement
     *            code du mode de paiement du bordereau à annuler
     * @param int $codeCaisse
     *            code caisse dans laquelle les paiements concernés seront placés
     * @return int Nombre de lignes du bordereau du depot annulé
     */
    public function annuleDepot($dateDepot, $dateBordereau, $codeModeDePaiement,
        $codeCaisse)
    {
        $where = new Where();
        $where->equalTo('dateDepot', $dateDepot)
            ->equalTo('codeModeDePaiement', $codeModeDePaiement)
            ->equalTo('dateBordereau', $dateBordereau);
        return $this->table_gateway->update(
            [
                'dateDepot' => null,
                'codeCaisse' => $codeCaisse
            ], $where);
    }

    /**
     * Donnne la dernière dateBordereau du lot de paiements filtré par les paramètres
     * donnés
     *
     * @param int $codeModeDePaiement
     *            code d'un mode de paiement
     * @param bool $encours
     *            si true, renvoie la date du bordereau en cours ou null s'il n'y en a pas
     * @return string format dateMysql
     */
    public function dateDernierBordereau($codeModeDePaiement, $encours = false)
    {
        $where = new Where();
        $where->equalTo('codeModeDePaiement', $codeModeDePaiement)->isNotNull(
            'dateBordereau');
        if ($encours) {
            $where->isNull('dateDepot');
        }
        $select = $this->table_gateway->getSql()->select();
        $select->columns([
            'date' => new Expression('max(dateBordereau)')
        ])->where($where);
        $result = $this->table_gateway->selectWith($select)->current();
        return $result->date;
    }

    /**
     * Donne la date du denier paiement
     *
     * @param int $codeModeDePaiement
     *
     * @return string format dateMysql
     */
    public function dateDernierPaiement($codeModeDePaiement)
    {
        $where = new Where();
        $where->equalTo('codeModeDePaiement', $codeModeDePaiement)->literal(
            'mouvement = 1');
        $select = $this->table_gateway->getSql()->select();
        $select->columns([
            'date' => new Expression('max(datePaiement)')
        ])->where($where);
        $result = $this->table_gateway->selectWith($select)->current();
        return $result->date;
    }

    /**
     * Renvoie le montant total d'un bordereau.
     * Par défaut, n'examine que les bordereaux
     * en cours. Si le codeModeDePaiement est null, renvoie le total des bordereaux en
     * cours.
     *
     * @param int|null $codeModeDePaiement
     *            code d'un mode de paiement
     * @param string|null $dateBordereau
     *            string : date au format dateMysql null (par défaut) : renvoie la somme
     *            du bordereau en cours
     * @return float
     */
    public function sommeBordereau($codeModeDePaiement, $dateBordereau = null)
    {
        $where = new Where();
        if (! is_null($codeModeDePaiement)) {
            $where->equalTo('codeModeDePaiement', $codeModeDePaiement);
        }
        if (is_null($dateBordereau)) {
            $where->isNull('dateDepot')->isNotNull('dateBordereau');
        } else {
            $where->equalTo('dateBordereau', $dateBordereau);
        }
        return $this->total($where);
    }

    /**
     *
     * @param int $millesime
     * @param int $codeCaisse
     * @param int $codeModeDePaiement
     * @return number|false
     */
    public function totalAnneeScolaire($millesime, $codeCaisse = null,
        $codeModeDePaiement = null)
    {
        if ($codeCaisse === false || $codeModeDePaiement === false) {
            return false;
        }
        $as = sprintf('%d-%d', $millesime, $millesime + 1);
        $where = new Where();
        $where->equalTo('anneeScolaire', $as);
        if (! is_null($codeCaisse)) {
            $where->equalTo('codeCaisse', $codeCaisse);
        }
        if (! is_null($codeModeDePaiement)) {
            $where->equalTo('codeModeDePaiement', $codeModeDePaiement);
        }
        return $this->total($where);
    }

    /**
     *
     * @param int $exercice
     * @param int $codeCaisse
     * @param int $codeModeDePaiement
     * @return number|false
     */
    public function totalExercice($exercice, $codeCaisse = null,
        $codeModeDePaiement = null)
    {
        if ($codeCaisse === false || $codeModeDePaiement === false) {
            return false;
        }
        $where = new Where();
        $where->equalTo('exercice', $exercice);
        if (! is_null($codeCaisse)) {
            $where->equalTo('codeCaisse', $codeCaisse);
        }
        if (! is_null($codeModeDePaiement)) {
            $where->equalTo('codeModeDePaiement', $codeModeDePaiement);
        }
        return $this->total($where);
    }

    /**
     *
     * @param \Zend\Db\Sql\Where $where
     * @return number|false
     */
    public function total(Where $where)
    {
        $select = $this->table_gateway->getSql()->select();
        $select->columns([
            'somme' => new Expression('sum(montant * mouvement)')
        ])->where($where);
        $result = $this->table_gateway->selectWith($select)->current();
        return $result->somme ?: 0;
    }

    /**
     * Refus bancaire d'honorer un chèque ou un abonnement CB ou annulation de recette
     *
     * @param int $paiementId
     */
    public function annule(int $paiementId)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'paiementId' => $paiementId,
                'mouvement' => 0,
                'dateRefus' => DateLib::nowToMysql()
            ]);
        $this->updateRecord($oData);
    }
}

