<?php
/**
 * Requêtes concernant la table `simulation-etablissements`
 * (déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\SimulationEtablissements')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Etablissement
 * @filesource SimulationEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

class SimulationEtablissements extends AbstractQuery
{

    protected function init()
    {
    }

    /**
     * Renvoie un paginator
     *
     * @param \Zend\Db\Sql\Where|array $where
     * @param string|array $order
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorSE($where, $order = [])
    {
        return $this->paginator($this->selectSE($where, $order));
    }

    protected function selectSE($filtre, $order = [])
    {
        $select1 = $this->sql->select();
        $select1->from(
            [
                'se' => $this->db_manager->getCanonicName('simulation-etablissements',
                    'table')
            ])
            ->join(
            [
                'eta1' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'se.origineId = eta1.etablissementId',
            [
                'etablissementorigine' => 'nom',
                'niveauetaborigine' => 'niveau'
            ])
            ->join([
            'cor' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor.communeId = eta1.communeId',
            [
                'communeetaborigineId' => 'communeId',
                'communeetaborigine' => 'nom'
            ])
            ->join(
            [
                'eta2' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'se.suivantId = eta2.etablissementId', [
                'etablissementsuivant' => 'nom'
            ])
            ->join([
            'csu' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta2.communeId = csu.communeId', [
            'communeetabsuivant' => 'nom'
        ])
            ->columns([
            'origineId',
            'suivantId'
        ]);
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

    public function getRecord($id)
    {
        $where = new Where();
        $where->equalTo('origineId', $id);
        return $this->renderResult($this->selectSE($where))
            ->current();
    }
}