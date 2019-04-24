<?php
/**
 * Renvoie un Predicate
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource PredicateDemandeR1NonTraitee.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Predicate as ZendPredicate;

class PredicateDemandeR1NonTraitee extends AbstractPredicate
{

    protected function init()
    {
        $predicate = new ZendPredicate(null, self::COMBINED_BY_AND);
        return $predicate->literal('ele.responsable1Id = r.responsableId')->literal(
            'sco.demandeR1 = 1');
    }
}
