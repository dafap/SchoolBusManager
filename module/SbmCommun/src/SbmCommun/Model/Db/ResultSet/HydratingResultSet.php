<?php
/**
 * Extension de Zend\Db\ResultSet\HydratingResultSet ajoutant une méthode pour renvoyer l'objectPrototype
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ResulSet
 * @filesource HydratingResulSet.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\ResultSet;

use Zend\Db\ResultSet\HydratingResultSet as ZendHydratingResultSet;

class HydratingResultSet extends ZendHydratingResultSet
{

    /**
     * Renvoie l'objectPrototype
     * 
     * @return mixed
     */
    public function getObjectPrototype()
    {
        return $this->objectPrototype;
    }
}