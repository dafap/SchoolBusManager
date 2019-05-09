<?php
/**
 * Requêtes concernant la table `secteur-scolaires-clg-pu`
 * (déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\SecteursScolairesClgPu')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Etablissement
 * @filesource SecteursScolairesClgPu.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

class SecteursScolairesClgPu extends AbstractQuery
{

    protected function init()
    {
    }

    public function getRecord($id)
    {
        $where = new Where();
        foreach ($id as $key => $value) {
            $where->equalTo($key, $value);
        }
        return $this->renderResult($this->selectSS($where))
            ->current();
    }

    /**
     * Renvoie un paginator
     *
     * @param \Zend\Db\Sql\Where|array $where
     * @param string|array $order
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorSS($where, $order = [])
    {
        return $this->paginator($this->selectSS($where, $order));
    }

    private function selectSS($filtre, $order = [])
    {
        $where = new Where();
        $where->literal('eta.niveau = 4')->literal('eta.statut = 1');

        $select1 = $this->sql->select();
        $select1->from(
            [
                'ss' => $this->db_manager->getCanonicName('secteurs-scolaires-clg-pu',
                    'table')
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'ss.etablissementId = eta.etablissementId', [
                'etablissement' => 'nom'
            ])
            ->join([
            'cet' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cet.communeId = eta.communeId', [
            'communeetab' => 'nom'
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'ss.communeId = com.communeId', [
            'commune' => 'nom'
        ])
            ->columns([
            'etablissementId',
            'communeId'
        ])
            ->where($where);
        if (! empty($filtre)) {
            $select = $this->sql->select();
            $select->from([
                'liste' => $select1
            ])
                ->where($filtre)
                ->order($order);
        } else {
            $select = $select1->order($order);
        }
        return $select;
    }
}