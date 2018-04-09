<?php
/**
 * Surcharge Zend\Db\Metadata\Source\Factory pour prendre en compte MysqlMetadata
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Metadata/Source
 * @filesource Factory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Source as ZendSource;
use Zend\Db\Exception\InvalidArgumentException;
use Zend\Db\Metadata\MetadataInterface;

/**
 * Source metadata factory.
 */
class Factory
{

    /**
     * Create source from adapter
     *
     * @param Adapter $adapter            
     * @return MetadataInterface
     * @throws InvalidArgumentException If adapter platform name not recognized.
     */
    public static function createSourceFromAdapter(Adapter $adapter)
    {
        $platformName = $adapter->getPlatform()->getName();
        
        switch ($platformName) {
            case 'MySQL':
                return new MysqlMetadata($adapter);
            case 'SQLServer':
                return new ZendSource\SqlServerMetadata($adapter);
            case 'SQLite':
                return new ZendSource\SqliteMetadata($adapter);
            case 'PostgreSQL':
                return new ZendSource\PostgresqlMetadata($adapter);
            case 'Oracle':
                return new ZendSource\OracleMetadata($adapter);
            default:
                throw new InvalidArgumentException(
                    "Unknown adapter platform '{$platformName}'");
        }
    }
}