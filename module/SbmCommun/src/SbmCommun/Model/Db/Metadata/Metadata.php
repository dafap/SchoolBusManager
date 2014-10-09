<?php
/**
 * Extension de la classe Zend\Db\Metadata\Metadata pour récupérer les informations sur les colonnes auto_increment 
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Metadata
 * @filesource Metadata.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Metadata;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata as ZendMetadata;
use Zend\Db\Metadata\Source as ZendSource;

class Metadata extends ZendMetadata
{
    /**
     * Surcharge de createSource
     *
     * @param  Adapter $adapter
     * @return Source\AbstractSource
     * @see Zend\Db\Metadata\Metadata
     */
    protected function createSourceFromAdapter(Adapter $adapter)
    {
        switch ($adapter->getPlatform()->getName()) {
            case 'MySQL':
                return new Source\MysqlMetadata($adapter);
            case 'SQLServer':
                return new ZendSource\SqlServerMetadata($adapter);
            case 'SQLite':
                return new ZendSource\SqliteMetadata($adapter);
            case 'PostgreSQL':
                return new ZendSource\PostgresqlMetadata($adapter);
        }
    
        throw new \Exception('cannot create source from adapter');
    }
}