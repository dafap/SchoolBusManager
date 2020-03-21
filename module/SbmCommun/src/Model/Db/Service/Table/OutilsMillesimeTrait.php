<?php
/**
 * Trait regroupant les méthodes communes aux tables lignes, services, etablissements-services et circuits
 * ainsi qu'aux tables scolarites et affectations
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Table
 * @filesource OutilsMillesimeTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use SbmCommun\Model\Traits\DebugTrait;

/**
 *
 * @author admin
 */
trait OutilsMillesimeTrait
{
    use DebugTrait;

    /**
     * Supprime tous les enregistrements concernant le millesime indiqué.
     *
     * @param int $millesime
     *
     * @return int
     */
    public function viderMillesime(int $millesime)
    {
        return $this->table_gateway->delete([
            'millesime' => $millesime
        ]);
    }

    /**
     * Duplique les enregistrements du millesime départ en millesime nouveau à condition
     * qu'il n'y ait aucun enregistrement dans le millesime nouveau. Renvoie true si c'est
     * bon, false s'il y avait déjà des enregistrements dans le millesime nouveau. En cas
     * d'erreur, lance une exception.
     *
     * @param int $millesime_depart
     * @param int $millesime_nouveau
     * @return bool
     */
    public function dupliquer(int $millesime_depart, int $millesime_nouveau): bool
    {
        if ($this->isEmptyMillesime($millesime_nouveau)) {
            $resultset = $this->fetchAll([
                'millesime' => $millesime_depart
            ]);
            //$this->debugInitLog('/debug', 'outil-millesime.log');
            $columns = $this->db_manager->getColumns($this->table_name, $this->table_type);
            $column_name = false;
            foreach ($columns as $ocolumn) {
                if ($ocolumn->getErrata('auto_increment')) {
                    $column_name = $ocolumn->getName();
                    break;
                }
            }
            //$this->debugLog($this->table_name);
            //$this->debugLog($column_name);
            foreach ($resultset as $item) {
                if ($column_name) {
                    $item->{$column_name} = null;
                }
                $item->millesime = $millesime_nouveau;
                $this->saveRecord($item);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Renvoie le dernier millesime utilisé dans cette table
     *
     * @return int
     */
    public function getDernierMillesime()
    {
        $select = $this->getTableGateway()
            ->getSql()
            ->select();
        $select->columns([
            'millesime' => new Expression('max(millesime)')
        ]);
        $resultset = $this->getTableGateway()->selectWith($select);
        $row = $resultset->current();
        return $row->millesime;
    }

    /**
     * Renvoie vrai si la table ne contient pas de données pour ce millésime.
     *
     * @param int $millesime
     *
     * @return boolean
     */
    public function isEmptyMillesime($millesime)
    {
        $resultset = $this->fetchAll([
            'millesime' => $millesime
        ]);
        return $resultset->count() == 0;
    }

    /**
     * Renvoie la liste des millesimes utilisés dans la table
     *
     * @return array
     */
    public function getMillesimes()
    {
        $select = $this->getTableGateway()
            ->getSql()
            ->select()
            ->columns([
            'millesime'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        $resultset = $this->getTableGateway()->selectWith($select);
        $array = [];
        foreach ($resultset as $row) {
            $label = $row->millesime == $this->db_manager->get('simulation') ? 'Simulation' : $row->millesime;
            $array[$row->millesime] = $label;
        }
        return $array;
    }
}

