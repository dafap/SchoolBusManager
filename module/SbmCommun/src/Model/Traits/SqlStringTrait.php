<?php
/**
 * Fournit la méthode getSqlString permettant donner le code SQL d'une requête.
 *
 * @project sbm
 * @package SbmCommun/src/Model/Traits
 * @filesource SqlStringTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Traits;

trait SqlStringTrait
{

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select|string
     *            Si string, alors c'est une méthode de la classe et args doit donner les
     *            paramètres de la méthode
     * @param array $args
     *
     * @return string
     */
    public function getSqlString($select, ...$args): string
    {
        if ($select instanceof string) {
            if (method_exists($this, $select)) {
                $select = $this->{$select}(...$args);
            } else {
                return $select . " n'est pas une méthode de la classe " . get_class($this);
            }
        }
        return $select->getSqlString($this->db_manager->getDbAdapter()
            ->getPlatform());
    }
}