<?php
/**
 * Renvoi un Where (Predicate) dont les conditions donnent les élèves gratuits
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource ElevesGratuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 août 2018
 * @version 2018-2.4.4
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

class ElevesGratuits extends Where
{

    private $millesime;

    private $alias;

    /**
     *
     * @param int $millesime            
     * @param string $alias
     *            nom ou alias de la table scolarites
     * @param array $predicates            
     * @param string $defaultCombination            
     */
    public function __construct($millesime, $alias = '', array $predicates = null, 
        $defaultCombination = self::COMBINED_BY_AND)
    {
        $this->millesime = $millesime;
        $this->alias = $alias;
        parent::__construct($predicates, $defaultCombination);
    }

    public function __invoke()
    {
        if ($this->alias) {
            $prefixe = $this->alias . '.';
        } else {
            $prefixe = '';
        }
        return $this->literal($prefixe . 'inscrit = 1')
            ->literal($prefixe . 'gratuit = 1')
            ->equalTo($prefixe . 'millesime', $this->millesime);
    }
}