<?php
/**
 * Renvoie un Predicate
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource PredicateAccordR2SansAffectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Predicate as ZendPredicate;

class PredicateAccordR2SansAffectation extends AbstractPredicate
{

    protected function init()
    {
        $predicate = new ZendPredicate([
            new PredicateSansAffectation()
        ], self::COMBINED_BY_AND);
        return $predicate->literal('ele.responsable2Id = r.responsableId')
            ->literal('sco.demandeR2 = 2')
            ->literal('sco.accordR2 = 1');
    }
}
