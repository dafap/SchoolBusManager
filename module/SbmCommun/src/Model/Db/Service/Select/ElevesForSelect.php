<?php
/**
 * Service fournissant une liste d'élèves sous la forme d'un tableau
 * 'eleveId' => 'libelle'
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Select
 * @filesource ElevesForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmCommun\Model\Db\Service\Select;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Literal;
use Zend\ServiceManager\FactoryInterface;

class ElevesForSelect extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var array
     */
    private $table_name = [];

    protected function init()
    {
        foreach ([
            'eleves',
            'scolarites',
            'responsables',
            'communes'
        ] as $name) {
            $this->table_name[$name] = $this->db_manager->getCanonicName($name, 'table');
        }
    }

    /**
     * Le libellé est de la forme 'nom prenom - grilleTarifaire (nb duplicatas)'
     *
     * @param \Zend\Db\Sql\Where $where
     * @return string[]
     */
    public function elevesAbonnes(Where $where = null)
    {
        $predicates = [
            new Literal('gratuit = 0'),
            new Literal('fa = 0')
        ];
        if ($where) {
            $predicates[] = $where;
        }
        $conditions = new \SbmCommun\Model\Db\Sql\Predicate\ElevesPayantsInscrits(
            $this->millesime, 'sco', $predicates);
        $select = $this->sql->select([
            'ele' => $this->table_name['eleves']
        ])
            ->columns(
            [
                'eleveId',
                'nomprenom' => new Literal(
                    'concat(ele.nom," ",ele.prenom," (",com.alias,")")')
            ])
            ->join([
            'sco' => $this->table_name['scolarites']
        ], 'ele.eleveId = sco.eleveId', [
            'grilleTarifR1',
            'reductionR1',
            'duplicataR1'
        ])
            ->join([
            'res' => $this->table_name['responsables']
        ], 'res.responsableId = ele.responsable1Id', [])
            ->join([
            'com' => $this->table_name['communes']
        ], 'com.communeId = res.communeId', [
            'alias'
        ])
            ->where($conditions())
            ->order([
            'ele.nom',
            'ele.prenom'
        ]);
        $this->addStrategy('grilleTarifR1',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        $result = [];
        foreach ($this->renderResult($select) as $row) {
            $result[$row['eleveId']] = sprintf('%s - %s %s (%d duplicata%s)',
                $row['nomprenom'], $row['grilleTarifR1'],
                $row['reductionR1'] ? 'Réduit' : 'Normal', $row['duplicataR1'],
                $row['duplicataR1'] > 1 ? 's' : '');
        }
        return $result;
    }

    public function inscrits()
    {
        $conditions = new \SbmCommun\Model\Db\Sql\Predicate\ElevesInscrits(
            $this->millesime, 'sco');
        $select = $this->sql->select([
            'ele' => $this->table_name['eleves']
        ])
            ->columns(
            [
                'eleveId',
                'nomprenom' => new Literal(
                    'concat(ele.nom," ",ele.prenom," (",com.alias,")")')
            ])
            ->join([
            'sco' => $this->table_name['scolarites']
        ], 'ele.eleveId = sco.eleveId', [])
            ->join([
            'res' => $this->table_name['responsables']
        ], 'res.responsableId = ele.responsable1Id', [])
            ->join([
            'com' => $this->table_name['communes']
        ], 'com.communeId = res.communeId', [])
            ->where($conditions())
            ->order([
            'ele.nom',
            'ele.prenom'
        ]);
        $result = [];
        foreach ($this->renderResult($select) as $row) {
            $result[$row['eleveId']] = $row['nomprenom'];
        }
        return $result;
    }
}