<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves avec des duplicatas
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesAvecDuplicatas.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesAvecDuplicatas extends AbstractElevesPredicate
{

    public function __invoke(): Where
    {
        if ($this->alias) {
            $prefixe = $this->alias . '.';
        } else {
            $prefixe = '';
        }
        return $this->literal($prefixe . 'inscrit = 1')
            ->literal($prefixe . 'duplicata > 0')
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}