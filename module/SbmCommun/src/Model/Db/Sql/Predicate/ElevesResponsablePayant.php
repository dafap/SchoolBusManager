<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves dont le responsable est payant
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesResponsablePayant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesResponsablePayant extends AbstractElevesPredicate
{

    /**
     * ATTENTION !
     * L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit l'élève en
     * payant. Le R2 ne compte pas pour ça.
     * @formatter off
     * Ceux qui ne sont pas en famille d'accueil et qui ne sont pas gratuit et qui sont
     * inscrits avec un accord ou une derogation ou qui ont payé et qui sont scolarisés
     * dans ce millesime. En fait on ne prend pas les préinscrits rayés ni les préinscrits
     * non ayant droit sans derogation mais on prend les inscrits rayés. Donc c'est le
     * contraire de :
     * <ul>
     * <li> préinscits et rayés (paiementR1 == 0 et inscrit == 0)</li>
     * <li> non ayant droit sans dérogation (accordR1 == 0 et accordR2 == 0 et derogation == 0)</li>
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
            ->nest()
            ->literal($prefixe . 'inscrit = 1')
            ->nest()
            ->literal($prefixe . 'accordR1 = 1')->or->literal($prefixe . 'accordR2 = 1')->or->literal(
            $prefixe . 'derogation > 0')
            ->unnest()
            ->unnest()->or->literal($prefixe . 'paiementR1 = 1')
            ->unnest()
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}