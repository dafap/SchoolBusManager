<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves dont le responsable est payant
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesResponsablePayant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesResponsablePayant extends AbstractElevesPredicate
{

    /**
     *
     * @formatter off
     * Ceux qui ne sont pas en famille d'accueil et qui ne sont pas gratuit et qui sont
     * inscrits ou qui ont payé et qui sont scolarisés dans ce millesime. En fait on ne
     * prend pas les préinscrits rayés mais on prend les inscrits rayés. Donc c'est le
     * contraire de :
     * <ul>
     * <li> préinscits et rayés (paiement == 0 et inscrit == 0)</li>
     * <li> en fa (fa == 1)</li>
     * <li> gratuits ou pris en charge par un organisme (gratuit > 0)</li>
     * </ul>
     * @formatter on
     *
     *
     * @return self (C'est un Where, un Predicate)
     */
    public function __invoke(): Where
    {
        if ($this->alias) {
            $prefixe = $this->alias . '.';
        } else {
            $prefixe = '';
        }
        return $this->literal($prefixe . 'fa = 0')
            ->literal($prefixe . 'selection = 0')
            ->literal($prefixe . 'gratuit = 0')
            ->nest()
            ->literal($prefixe . 'inscrit = 1')->or->literal($prefixe . 'paiement = 1')
            ->unnest()
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}