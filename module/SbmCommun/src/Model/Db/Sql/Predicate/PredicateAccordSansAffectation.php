<?php
/**
 * Renvoie un Predicate
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource PredicateAccordSansAffectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Predicate as ZendPredicate;

class PredicateAccordSansAffectation extends AbstractPredicate
{

    public function init()
    {
        return new ZendPredicate(
            [
                new PredicateAccordR1SansAffectation(),
                new PredicateAccordR2SansAffectation()
            ], self::COMBINED_BY_OR);
    }
}
