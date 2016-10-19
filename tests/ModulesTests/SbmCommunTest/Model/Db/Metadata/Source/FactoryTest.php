<?php
/**
 * Reprise du test de Zend Framework (http://framework.zend.com/)
 *
 * 
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Metadata/Factory
 * @filesource FactoryTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 juil. 2016
 * @version 2016-2.1.10
 */
namespace SbmCommun\Model\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;
use SbmCommun\Model\Db\Metadata\Source\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider validAdapterProvider
     *
     * @param Adapter $adapter            
     * @param string $expectedReturnClass            
     */
    public function testCreateSourceFromAdapter(Adapter $adapter, $expectedReturnClass)
    {
        $source = Factory::createSourceFromAdapter($adapter);
        $this->assertInstanceOf('Zend\Db\Metadata\MetadataInterface', $source);
        $this->assertInstanceOf($expectedReturnClass, $source);
    }

    public function validAdapterProvider()
    {
        $createAdapterForPlatform = function ($platformName) {
            $platform = $this->getMock('Zend\Db\Adapter\Platform\PlatformInterface');
            $platform->expects($this->any())
                ->method('getName')
                ->willReturn($platformName);
            $adapter = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
                ->disableOriginalConstructor()
                ->getMock();
            $adapter->expects($this->any())
                ->method('getPlatform')
                ->willReturn($platform);
            return $adapter;
        };
        return [
            // Description => [adapter, expected return class]
            'MySQL' => [
                $createAdapterForPlatform('MySQL'),
                'SbmCommun\Model\Db\Metadata\Source\MysqlMetadata'
            ],
            'SQLServer' => [
                $createAdapterForPlatform('SQLServer'),
                'Zend\Db\Metadata\Source\SqlServerMetadata'
            ],
            'SQLite' => [
                $createAdapterForPlatform('SQLite'),
                'Zend\Db\Metadata\Source\SqliteMetadata'
            ],
            'PostgreSQL' => [
                $createAdapterForPlatform('PostgreSQL'),
                'Zend\Db\Metadata\Source\PostgresqlMetadata'
            ],
            'Oracle' => [
                $createAdapterForPlatform('Oracle'),
                'Zend\Db\Metadata\Source\OracleMetadata'
            ]
        ];
    }
} 