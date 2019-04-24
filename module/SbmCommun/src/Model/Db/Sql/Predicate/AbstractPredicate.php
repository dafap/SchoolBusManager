<?php
/**
 * Classe abstraite pour construire les Predicate de ce dossier
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql/Predicate
 * @filesource AbstractPredicate.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Sql\Predicate;

use Zend\Db\Sql\Predicate\Predicate as ZendPredicate;

abstract class AbstractPredicate extends ZendPredicate
{

    public function __construct(array $predicates = null,
        $defaultCombination = self::COMBINED_BY_AND)
    {
        $predicates = (array) $predicates;
        $predicates[] = $this->init();
        parent::__construct($predicates, $defaultCombination);
    }

    abstract protected function init();
}
