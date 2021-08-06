<?php
/**
 * Requêtes sur la table système `history`
 *
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/History
 * @filesource History.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
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

    public function getPaiementsChanges(int $exercice, int $paiementId = null)
    {
        return $this->renderResult($this->selectPaiementsChanges($exercice, $paiementId));
    }

    public function paginatorPaiementsChanges(int $exercice, int $paiementId = null)
    {
        return $this->paginator($this->selectPaiementsChanges($exercice, $paiementId));
    }

    /**
     * SELECT * FROM sbm_s_history WHERE table_name='sbm_t_paiements' AND id_txt=2019 AND
     * (action = 'delete' OR (action = 'update' AND (id_int, dt) NOT IN ( SELECT id_int,
     * dt FROM `sbm_s_history` WHERE table_name='sbm_t_paiements' AND id_txt=2019 AND
     * `action`='delete')))
     *
     * @param int $exercice
     * @param int $paiementId
     * @return \Zend\Db\Sql\Select
     */
    protected function selectPaiementsChanges(int $exercice, int $paiementId = null)
    {
        // sous requête 'les delete'
        $where1 = new Where();
        $where1->equalTo('table_name', $this->db_manager->getCanonicName('paiements'))
            ->equalTo('action', 'delete')
            ->equalTo('id_txt', $exercice);
        $select1 = $this->sql->select($this->history_name)
            ->columns([
            'id_int',
            'dt'
        ])
            ->where($where1);
        // requête principale
        $where = new Where();
        $where->equalTo('table_name', $this->db_manager->getCanonicName('paiements'))
            ->equalTo('id_txt', $exercice)
            ->nest()
            ->equalTo('action', 'delete')->OR->nest()->equalTo('action', 'update')->AND->notIn(
            [
                'id_int',
                'dt'
            ], $select1)
            ->unnest()
            ->unnest();
        if ($paiementId) {
            $where->equalTo('id_int', $paiementId);
        }
        return $this->sql->select($this->history_name)
            ->columns([
            'action',
            'id_int',
            'dt',
            'log'
        ])
            ->where($where)
            ->order([
            'dt'
        ]);
    }
}