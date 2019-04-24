<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves en garde alternée
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource ElevesEnGA.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesEnGA extends AbstractElevesPredicate
{

    public function __invoke(): Where
    {
        if ($this->alias) {
            $prefixe = $this->alias . '.';
        } else {
            $prefixe = '';
        }
        return $this->literal($prefixe . 'inscrit = 1')
            ->literal($prefixe . 'selection = 0')
            ->isNotNull('responsable2Id')
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}