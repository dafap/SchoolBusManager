<?php
/**
 * Classe abstraite à dériver pour obtenir les predicates de ce dossier
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Sql/Predicate
 * @filesource AbstractElevesPredicate.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Where;

abstract class AbstractElevesPredicate extends Where
{

    protected $millesime;

    protected $alias;

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

    abstract public function __invoke(): Where;
}