<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves payants
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesPayantsInscrits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesPayantsInscrits extends AbstractElevesPredicate
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
            ->literal($prefixe . 'fa = 0')
            ->nest()
            ->literal($prefixe . 'paiement = 1')->or->literal($prefixe . 'gratuit = 2')
            ->unnest()
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}