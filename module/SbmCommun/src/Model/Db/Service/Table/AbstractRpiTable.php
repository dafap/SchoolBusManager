<?php
/**
 * Méthodes communes aux tables RpiCommunes, RpiEtablissement et RpiClasses
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Table
 * @filesource AbstractRpiTable.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;

abstract class AbstractRpiTable extends AbstractSbmTable
{


    /**
     * Insère l'ObjectData passé en paramètre dans la table.
     * Retourne le nombre de lignes insérées ou false en cas d'exception.
     * Retourne 0 si l'entité était déjà présente.
     *
     * @param ObjectDataInterface $data
     *            objet de données à insérer
     *
     * @return int|boolean
     */
    public function insertRecord(ObjectDataInterface $obj_data)
    {
        try {
            return $this->table_gateway->insert($obj_data->getArrayCopy());
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            if ($e->getPrevious()->getCode() == 23000) {
                return 0;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
 