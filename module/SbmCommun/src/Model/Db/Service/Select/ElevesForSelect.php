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
 * @date 24 avr. 2019
 * @version 2019-5.0
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
            'scolarites'
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
            new Literal('gratuit = 0')
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
                'nomprenom' => new Literal('concat(nom, " ", prenom)')
            ])
            ->join([
            'sco' => $this->table_name['scolarites']
        ], 'ele.eleveId = sco.eleveId', [
            'grilleTarif',
            'duplicata'
        ])
            ->where($conditions())
            ->order([
            'nom',
            'prenom'
        ]);
        $this->addStrategy('grilleTarif',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        $result = [];
        foreach ($this->renderResult($select) as $row) {
            $result[$row['eleveId']] = sprintf('%s - %s (%d duplicatas)',
                $row['nomprenom'], $row['grilleTarif'], $row['duplicata']);
        }
        return $result;
    }
}