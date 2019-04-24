<?php
/**
 * Requêtes sur la table système `history`
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/History
 * @filesource History.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\History;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

class History extends AbstractQuery
{

    /**
     *
     * @var string
     */
    private $history_name;

    protected function init()
    {
        $this->history_name = $this->db_manager->getCanonicName('history', 'sys');
    }

    /**
     * Changements du dernier jour On vérifie si la table affectation a été modifié pour
     * le millesime indiqué
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLastDayChanges($table_name, $millesime)
    {
        $select = $this->sql->select($this->history_name);
        $table_affectations = $this->db_manager->getCanonicName($table_name);
        $hier = date('Y-m-d H:i', strtotime('-1 day'));
        $where = new Where();
        $where->equalTo('table_name', $table_affectations)
            ->greaterThanOrEqualTo('dt', $hier)
            ->like('id_txt', "$millesime%");
        $select->columns([
            'id_txt',
            'log'
        ])
            ->where($where)
            ->order([
            'dt'
        ]);
        return $this->renderResult($select);
    }
}