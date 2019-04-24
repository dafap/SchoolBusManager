<?php
/**
 * Renvoie un Predicate
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource PredicateDemandeNonTraitee.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Predicate as ZendPredicate;

class PredicateDemandeNonTraitee extends AbstractPredicate
{

    protected function init()
    {
        return new ZendPredicate(
            [
                new PredicateDemandeR1NonTraitee(),
                new PredicateDemandeR2NonTraitee()
            ], self::COMBINED_BY_OR);
    }
}
