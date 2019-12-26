<?php
/**
 * Gestion de la table système `libelles
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource Libelles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 déc. 2019
 * @version 2019-2.5.4
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Literal;

class Libelles extends AbstractSbmTable
{

    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'libelles';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\Libelles';
        $this->id_name = [
            'nature',
            'code'
        ];
    }

    /**
     * Soit on passe un tableau des valeurs correspondant à la propriété id_name (nature, code),
     * soit on passe une chaine contenant ces valeurs séparées par |
     *
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::getRecord()
     */
    public function getRecord($id)
    {
        if (is_array($id)) {
            return parent::getRecord($id);
        } elseif (is_string($id)) {
            list ($nature, $code) = explode('|', $id);
            return parent::getRecord([
                'nature' => $nature,
                'code' => $code
            ]);
        }
    }

    public function saveRecordAvecControle(ObjectDataInterface $obj_data, $edit = false,
        $id = null)
    {
        $nature = $obj_data->nature;
        $code = $obj_data->code;

        $cle = $nature . '|' . $code;
        if ($edit) {
            if ($id == $cle) {
                parent::saveRecord($obj_data);
                $ok = true;
            } elseif ($this->is_newRecord([
                'nature' => $nature,
                'code' => $code
            ])) {
                list ($old_nature, $old_code) = explode('|', $id);
                parent::deleteRecord([
                    'nature' => $old_nature,
                    'code' => $old_code
                ]);
                parent::saveRecord($obj_data);
                $ok = true;
            } else {
                $ok = false;
            }
        } else {
            if ($this->is_newRecord([
                'nature' => $nature,
                'code' => $code
            ])) {
                parent::saveRecord($obj_data);
                $ok = true;
            } else {
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * Renvoie tous les libellés ouverts
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function fetchOpen()
    {
        $where = new Where([
            new Literal('ouvert = 1')
        ]);
        $order = [
            'nature',
            'code'
        ];
        return $this->fetchAll($where, $order);
    }

    /**
     * Renvoie le code d'un libellé pour une nature donnée.
     *
     * @param string $nature
     * @param string $libelle
     *
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException
     *
     * @return integer
     */
    public function getCode($nature, $libelle)
    {
        $where = new Where();
        $rowset = $this->fetchAll(
            $where->equalTo('nature', $nature)
                ->equalTo('libelle', $libelle));
        if ($rowset->count() == 0) {
            throw new Exception\RuntimeException("Le libellé '$libelle' n'existe pas.");
        } else {
            return $rowset->current()->code;
        }
    }

    /**
     * Renvoie un tableau indexé donnant les codes de la nature demandée à partir de leur libellé
     *
     * @param string $nature
     *
     * @return array;
     */
    public function getCodes(string $nature)
    {
        $where = new Where();
        $rowset = $this->fetchAll($where->equalTo('nature', $nature));
        $array = [];
        foreach ($rowset as $row) {
            $array[$row->libelle] = $row->code;
        };
        return $array;
    }

    /**
     * Renvoie le libellé d'un code pour une nature donnée.
     *
     * @param string $nature
     * @param int $code
     *
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException
     *
     * @return string
     */
    public function getLibelle($nature, $code)
    {
        $where = new Where();
        $rowset = $this->fetchAll(
            $where->equalTo('nature', $nature)
                ->equalTo('code', $code));
        if (! $rowset) {
            throw new Exception\RuntimeException('Ce code n\'existe pas.');
        } else {
            return $rowset->current()->libelle;
        }
    }
}