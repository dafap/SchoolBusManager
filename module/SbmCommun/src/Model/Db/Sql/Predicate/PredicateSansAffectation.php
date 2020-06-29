<?php
/**
 * Renvoie un Predicate
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource PredicateSansAffectation.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Predicate as ZendPredicate;

class PredicateSansAffectation extends AbstractPredicate
{

    protected function init()
    {
        $predicate = new ZendPredicate();
        return $predicate->isNull('aff1R1.eleveId')->or->isNull('aff3R1.eleveId')->or->nest()
            ->literal('cla.niveau >= 4')
            ->isNull('aff2R1.eleveId')
            ->unnest()->or->nest()
            ->literal('sco.demandeR2 > 0')
            ->nest()
            ->isNull('aff1R2.eleveId')->or->isnull('aff3R2.eleveId')->or->nest()
            ->literal('cla.niveau >= 4')
            ->isNull('aff2R2.eleveId')
            ->unnest()
            ->unnest()
            ->unnest();
    }
}
