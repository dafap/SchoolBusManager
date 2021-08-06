<?php
/**
 * Requêtes pour extraire des etablissements
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Etablissement
 * @filesource Etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

class Etablissements extends AbstractQuery
{

    protected function init()
    {
    }

    /**
     * Requête préparée renvoyant la position géographique des établissements,
     *
     * @param Where $where
     * @param string $order
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLocalisation(Where $where, $order = null)
    {
        return $this->renderResult($this->selectLocalisation($where, $order));
    }

    protected function selectLocalisation(Where $where, $order = null)
    {
        $select = clone $this->sql->select();
        $select->from(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ])
            ->columns([
            'nom',
            'x',
            'y'
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId=com.communeId', [
            'commune' => 'nom'
        ]);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}
