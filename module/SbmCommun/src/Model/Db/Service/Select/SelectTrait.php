<?php
/**
 * Méthode utile pour le débuggage
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Select
 * @filesource SelectTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 juin 2019
 * @version 2019-2.5.0
 */

namespace SbmCommun\Model\Db\Service\Select;

use Zend\Db\Sql\Select;

trait SelectTrait
{
    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return string
     */
    public function getSqlString(Select $select): string
    {
        return $select->getSqlString($this->db_manager->getDbAdapter()
            ->getPlatform());
    }
}