<?php
/**
 * Requêtes pour les statistiques concernant les paiements
 * (classe déclarée dans mocule.config.php sous l'alias 'Sbm\Statistiques\Paiement')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Paiement
 * @filesource Statistiques.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmCommun\Model\Db\Service\Query\Paiement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;

class Statistiques extends AbstractQuery
{

    /**
     * Modes de paiement
     *
     * @var array Tableau de la forme [code => libellé, ...]
     */
    protected $modes;

    protected function init()
    {
    }

    /**
     * Renvoie un tableau des sommes enregistrées par année scolaire et mode de paiement
     * Si le millesime est donné, une seule année scolaire est renvoyée.
     *
     * SELECT libelle, sum(montant)
     * FROM `sbm_t_paiements` p
     * JOIN `sbm_v_libelles-modes-de-paiement` m ON m.code=p.codeModeDePaiement
     * WHERE anneeScolaire='2014-2015'
     * GROUP BY anneeScolaire, codeModeDePaiement
     *
     * @param int $millesime
     *
     * @return array
     */
    public function getSumByAsMode($millesime = null)
    {
        $result = $this->renderResult($this->selectSumByAsMode($millesime));
        $totalASMode = [];
        $totalAS = [];
        $totalGeneral = 0;
        foreach ($result as $row) {
            $totalASMode[$row['anneeScolaire']][$row['mode']] = $row['somme'];
            if (isset($totalAS[$row['anneeScolaire']])) {
                $totalAS[$row['anneeScolaire']] += $row['somme'];
            } else {
                $totalAS[$row['anneeScolaire']] = $row['somme'];
            }
            $totalGeneral += $row['somme'];
        }
        return [
            'totalGeneral' => $totalGeneral,
            'totalAS' => $totalAS,
            'totalASMode' => $totalASMode
        ];
    }

    protected function selectSumByAsMode($millesime = null)
    {
        $select = $this->sql->select(
            [
                'p' => $this->db_manager->getCanonicName('paiements', 'table')
            ]);
        $select->columns([
            'anneeScolaire',
            'somme' => new Literal('sum(montant)')
        ])
            ->join(
            [
                'm' => $this->db_manager->getCanonicName('libelles-modes-de-paiement',
                    'vue')
            ], 'm.code=p.codeModeDePaiement', [
                'mode' => 'libelle'
            ])
            ->group([
            'anneeScolaire',
            'libelle'
        ]);

        if (isset($millesime)) {
            $where = new Where();
            $as = $millesime . '-' . ($millesime + 1);
            $where->equalTo('anneeScolaire', $as);
            $select->where($where);
        }
        return $select;
    }

    public function getSumByExerciceMode($millesime = null)
    {
        ;
    }
}