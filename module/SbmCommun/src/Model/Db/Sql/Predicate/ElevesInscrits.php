<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves préinscrits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesInscrits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 juin 2021
 * @version 2021-2.6.2
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesInscrits extends AbstractElevesPredicate
{

    /**
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant.
     * Le R2 ne compte pas pour ça.
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
            ->equalTo($prefixe . 'millesime', $this->millesime)
            ->nest()
            ->literal($prefixe . 'gratuit > 0')->or->nest()
            ->literal($prefixe . 'demandeR1 > 0')
            ->literal($prefixe . 'paiementR1 = 1')
            ->unnest()
            ->unnest();
    }
}