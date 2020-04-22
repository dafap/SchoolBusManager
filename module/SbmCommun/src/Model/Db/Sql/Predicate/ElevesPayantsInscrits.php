<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves payants
 * (famille, fa ou organisme)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesPayantsInscrits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesPayantsInscrits extends AbstractElevesPredicate
{

    /**
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * {@inheritdoc}
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
            ->nest()
            ->literal($prefixe . 'paiementR1 = 1')->or->literal($prefixe . 'gratuit = 2')
            ->unnest()
            ->equalTo($prefixe . 'millesime', $this->millesime)
            ->nest()
            ->nest()
            ->literal($prefixe . 'demandeR1 > 0')
            ->literal($prefixe . 'accordR1 = 1')
            ->unnest()
            ->or
            ->nest()
            ->literal($prefixe . 'demandeR2 > 0')
            ->literal($prefixe . 'accordR2 = 1')
            ->unnest()
            ->unnest();
    }
}