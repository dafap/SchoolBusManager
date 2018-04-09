<?php
/**
 * Extension de Zend\Db\ResultSet\HydratingResultSet ajoutant une méthode pour renvoyer l'objectPrototype
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/ResulSet
 * @filesource HydratingResulSet.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 * @deprecated
 */
namespace SbmCommun\Model\Db\ResultSet;

use Zend\Db\ResultSet\HydratingResultSet as ZendHydratingResultSet;

/**
 *
 * @deprecated
 *
 */
class HydratingResultSet extends ZendHydratingResultSet
{

    /**
     * Renvoie l'objectPrototype
     *
     * @return mixed
     */
    public function getObjectPrototype()
    {
        trigger_error(
            sprintf(
                'Cette classe %s ne devrait plus être utilisée car le ZendFramework propose' .
                     ' la méthode getObjectPrototype() depuis la version 2.4.0 dans la classe' .
                     ZendHydratingResultSet::class, get_class($this)), E_USER_DEPRECATED);
        return $this->objectPrototype;
    }
}