<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves préinscrits
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesPreinscrits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesPreinscrits extends AbstractElevesPredicate
{

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
            ->literal($prefixe . 'paiement = 0')
            ->literal($prefixe . 'fa = 0')
            ->literal($prefixe . 'gratuit = 0')
            ->unnest()->or->nest()
            ->literal($prefixe . 'district = 0')
            ->literal($prefixe . 'derogation = 0')
            ->unnest()
            ->unnest();
    }
}