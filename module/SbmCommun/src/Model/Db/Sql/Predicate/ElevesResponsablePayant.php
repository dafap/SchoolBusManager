<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves dont le responsable est payant
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesResponsablePayant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesResponsablePayant extends AbstractElevesPredicate
{

    /**
     * ATTENTION ! L'élève est inscrit si paiementR1 == 1 car c'est le R1 qui inscrit
     * l'élève en payant. Le R2 ne compte pas pour ça.
     *
     * @formatter off
     * Ceux qui sont scolarisés dans ce millesime, qui ne sont pas en attente, pas gratuits,
     * qui sont inscrits ou qui ont payé (même s'il ont été rayé par la suite) et pour lesquel
     * on a une réponse positive à leur demande de transport.
     * En fait on ne prend pas les préinscrits rayés, ni ceux mis en attente, ni ceux pour lesquels
     * on n'a pas de solution de transport, ni les gratuits.
     * Donc c'est le contraire de :
     * <ul>
     * <li> préinscits et rayés (paiementR1 == 0 et inscrit == 0)</li>
     * <li> demandes sans solution (demandeR1 > 0 et accordR1 == 0 et demandeR2 > 0 et accordR2 == 0)</li>
     * <li> gratuits (gratuit = 1)</li>
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
        return $this->equalTo($prefixe . 'millesime', $this->millesime)
            ->literal($prefixe . 'selection = 0')
            ->literal($prefixe . 'gratuit <> 1')
            ->nest()
            ->literal($prefixe . 'inscrit = 1')->or->literal($prefixe . 'paiementR1 = 1')
            ->unnest()
            ->nest()
            ->nest()
            ->literal($prefixe . 'demandeR1 > 0')
            ->literal($prefixe . 'accordR1 = 1')
            ->unnest()->or->nest()
            ->literal($prefixe . 'demandeR2 > 0')
            ->literal($prefixe . 'accordR2 = 1')
            ->unnest()
            ->unnest();
    }
}