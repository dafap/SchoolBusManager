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
 * @date 17 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesSansPreinscrits extends AbstractElevesPredicate
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
            ->literal($prefixe . 'district = 1')->or->literal($prefixe . 'derogation >= 1')
            ->unnest()
            ->nest()
            ->literal($prefixe . 'paiement = 1')->or->literal($prefixe . 'fa = 1')->or->literal(
            $prefixe . 'gratuit > 0')->unnest();
    }
}