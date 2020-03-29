<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves préinscrits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesPreinscrits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesPreinscrits extends AbstractElevesPredicate
{

    /**
     * ATTENTION !
     * L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit l'élève en
     * payant. Le R2 ne compte pas pour ça.
     *
     * {@inheritDoc}
     * @see \SbmCommun\Model\Db\Sql\Predicate\AbstractElevesPredicate::__invoke()
     */
    public function __invoke(): Where
    {
        if ($this->alias) {
            $prefixe = $this->alias . '.';
        } else {
            $prefixe = '';
        }
        return $this->literal($prefixe . 'inscrit = 1')
            ->literal($prefixe . 'selection = 0')
            ->equalTo($prefixe . 'millesime', $this->millesime)
            ->nest()
            ->nest()
            ->literal($prefixe . 'paiementR1 = 0')
            ->literal($prefixe . 'fa = 0')
            ->literal($prefixe . 'gratuit = 0')
            ->unnest()->or->nest()
            ->literal($prefixe . 'district = 0')
            ->literal($prefixe . 'derogation = 0')
            ->unnest()
            ->unnest();
    }
}