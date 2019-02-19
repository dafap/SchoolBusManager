<?php
/**
 * Test de création du service de configuration des mails dans SBM
 *
 * @project sbm
 * @package ModulesTests/SbmMailTest/Model/Service
 * @filesource ConfigServiceFactoryTest.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2016
 * @version 2016-2.1.10
 */
namespace ModulesTests\SbmMailTest\Model\Service;

use PHPUnit_Framework_TestCase;
use SbmMail\Model\Service\ConfigServiceFactory;

class ConfigServiceFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $ma_config = [
            'sbm' => [
                'mail' => [
                    'foo' => 'bar'
                ]
            ]
        ];
        $factory = new ConfigServiceFactory();
        $serviceLocator = $this->createMock(
            'Zend\\ServiceManager\\ServiceLocatorInterface');
        $serviceLocator->expects($this->any())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($ma_config));
        $this->assertSame($ma_config['sbm']['mail'], 
            $factory->createService($serviceLocator));
    }
}