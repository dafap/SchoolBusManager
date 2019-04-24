<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves en famille d'accueil
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource ElevesEnFA.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesEnFA extends AbstractElevesPredicate
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
            ->literal($prefixe . 'paiement = 0')
            ->literal($prefixe . 'fa = 1')
            ->literal($prefixe . 'gratuit <> 1')
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}