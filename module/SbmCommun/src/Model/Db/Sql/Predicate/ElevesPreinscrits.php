<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves préinscrits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesPreinscrits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesPreinscrits extends AbstractElevesPredicate
{

    /**
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Sql\Predicate\AbstractElevesPredicate::__invoke()
     */
    public function __invoke(int $r = 1): Where
    {
        if ($this->alias) {
            $prefixe = $this->alias . '.';
        } else {
            $prefixe = '';
        }
        if ($r == 1) {
            $this->literal($prefixe . 'gratuit <> 1')->literal(
                $prefixe . 'paiementR1 = 0');
        } else {
            $this->literal($prefixe . 'paiementR2 = 0');
        }
        return $this->literal($prefixe . 'inscrit = 1')
            ->literal($prefixe . 'selection = 0')
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}