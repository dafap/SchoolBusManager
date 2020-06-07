<?php
/**
 * Requêtes portant sur les transporteurs
 * (classe déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\Transporteurs')
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Transporteur
 * @filesource Transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juin 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Transporteur;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Transporteurs extends AbstractQuery
{

    protected function init()
    {
    }

    /**
     * Renvoie la liste des emails des utilisateurs associés à un transporteur
     *
     * @param int $transporteurId
     * @param string|array $order
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getUserEmails($transporteurId, $order = null)
    {
        return $this->renderResult($this->selectUserEmails($transporteurId, $order));
    }

    protected function selectUserEmails($transporteurId, $order)
    {
        $select = $this->sql->select(
            [
                'ut' => $this->db_manager->getCanonicName('users-transporteurs', 'table')
            ])
            ->join([
            'u' => $this->db_manager->getCanonicName('users', 'table')
        ], 'u.userId = ut.userId', [
            'email'
        ])
            ->columns([
            'nomprenom' => new Expression('CONCAT(u.prenom, " ", u.nom)')
        ]);
        if (! empty($order)) {
            $select->order($order);
        }
        $where = new Where();
        $where->equalTo('ut.transporteurId', $transporteurId);
        // die($this->getSqlString($select));
        return $select->where($where);
    }
}