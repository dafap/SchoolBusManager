<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves du millésime indiqué
 * sans les préinscrits, sans les mis en attente et sans les rayés
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource ElevesSansPreinscrits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesSansPreinscrits extends AbstractElevesPredicate
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
            ->equalTo($prefixe . 'millesime', $this->millesime)
            ->nest()
            ->literal($prefixe . 'paiementR1 = 1')->or->literal($prefixe . 'gratuit = 1')->unnest();
    }
}