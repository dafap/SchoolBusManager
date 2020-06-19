<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves avec des duplicatas
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesAvecDuplicatas.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesAvecDuplicatas extends AbstractElevesPredicate
{

    /**
     * Par défaut, pour le responsable 1
     *
     * @param int $r
     *            1 ou 2 pour responsable1 ou responsable2
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Sql\Predicate\AbstractElevesPredicate::__invoke()
     */
    public function __invoke(int $r = 1): Where
    {
        if ($this->alias) {
            $prefixe = $this->alias . '.';
        } else {
            $prefixe = '';
        }
        return $this->literal($prefixe . 'inscrit = 1')
            ->literal($prefixe . sprintf('duplicataR%d > 0', $r))
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}