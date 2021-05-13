<?php
/**
 * Surcharge la classe Zend\Db\Sql\Insert pour traiter l'instruction REPLACE
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Sql
 * @filesource Replace.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\Db\Sql;

use Zend\Db\Sql\Insert;

class Replace extends Insert
{

    const SPECIFICATION_UNION = 'REPLACE INTO %1$s %2$s SELECT * FROM (%3$s) AS tmp';

    /**
     * @param \Zend\Db\Sql\Select $union
     */
    public function setUnion($union)
    {
        $this->specifications[self::SPECIFICATION_SELECT] = self::SPECIFICATION_UNION;
        $this->select = $union;
        return $this;
    }
}